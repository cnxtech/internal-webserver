#!/usr/bin/env python
"""Check for routes that never return ok response codes

Notifies slack and alerta when routes are found that have responses, but no 2xx
or 3XX responses in a day.

Note we consider 401s, 404s and 405s to not be errors, since they are due to
client error rather than broken routes.

Run by cron once per day.
"""
import argparse
import datetime
import logging

import alertlib
import bq_util
import initiatives

# Report on the previous day by default
_DEFAULT_DAY = datetime.datetime.utcnow() - datetime.timedelta(1)


ROUTES_EXPECTED_TO_FAIL = frozenset((
    'main:/crash',
    '/_ah/start.*',  # Logs show this as having null status
    'api_main:/api/internal/graphql [POST]',  # Most valid graphql queries will
    'api_main:/api/internal/graphql',         # include query name in route.
    'api_main:/api/internal/graphql [HEAD]',
))


QUERY = """\
SELECT
  route,
  ok_reqs,
  bot_reqs,
  num_ips,
  total_reqs
FROM (
  SELECT
    elog_url_route AS route,
    SUM((status >= 200 AND status < 400) OR status IN (401, 404, 405, 501))
        AS ok_reqs,
    SUM(elog_device_type is NULL or elog_device_type = "bot/dev")
        AS bot_reqs,
    COUNT(distinct IP) AS num_ips,
    SUM(1) AS total_reqs
  FROM
    [khanacademy.org:deductive-jet-827:logs.requestlogs_{}]
  GROUP BY
    route)
WHERE
  ok_reqs = 0
  AND total_reqs > 0
  -- We ignore errors that are just from bots
  AND total_reqs > bot_reqs
  -- If it's just one bad IP, it's likely just a bad client of some
  -- sort, so we ignore it.
  AND num_ips > 1
"""


def _plural(s, num):
    # Totally skeezy, but works for our purposes.
    return s if num == 1 else s + 's'


def _errors(route_data):
    return [
        '`{}` owned by {} ({} {} total, {} of them bots, {} unique {})'.format(
            d['route'],
            ', '.join(d['owners']),
            d['total_reqs'], _plural('request', d['total_reqs']),
            d['bot_reqs'],
            d['num_ips'], _plural('IP', d['num_ips']))
        for d in route_data
    ]


def notify(route_data, date):
    msg = '{} did not return any 2xx responses on {}:\n{}'.format(
        _plural('Route', len(route_data)),
        date.strftime('%x'), '\n'.join(_errors(route_data)))
    alert = alertlib.Alert(msg, severity=logging.ERROR)
    alert.send_to_slack(channel='#infrastructure-sre', simple_message=True)


def check(date, dry_run=False):
    yyyymmdd = date.strftime("%Y%m%d")
    q = QUERY.format(yyyymmdd)
    data = bq_util.query_bigquery(q)
    route_data = [row for row in data
                  if not row['route'] in ROUTES_EXPECTED_TO_FAIL]

    for row in route_data:
        route = row['route']
        row['owners'] = initiatives.route_owners(route)
    if dry_run:
        if not route_data:
            print 'No routes with no 2xx requests for {}'.format(
                date.strftime('%x'))
        else:
            print 'Routes with no 2xx requests for {}:\n{}'.format(
                date.strftime('%x'), '\n'.join(_errors(route_data)))
        return

    if route_data:
        notify(route_data, date)


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--date', metavar='YYYYMMDD',
                        default=_DEFAULT_DAY.strftime("%Y%m%d"),
                        help=('Date to get reports for, specified as YYYYMMDD '
                              '(default "%(default)s")'))
    parser.add_argument('--dry-run', '-n', action='store_true',
                        help="Don't actually send alerts")
    args = parser.parse_args()
    date = datetime.datetime.strptime(args.date, "%Y%m%d")
    check(date, dry_run=args.dry_run)


if __name__ == '__main__':
    main()
