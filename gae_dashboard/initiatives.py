"""Initiatives information including route and file ownership.

The data is generated by webapp/dev/owership and stored in GCS.
"""
import json
import os
import os.path
import re
import subprocess
import time
import urlparse

# TODO(amos): Maybe eventully move these email addresses to
# dev.ownership._TEAMS. The issue is that some of these aren't general purpose
# email addresses, rather they are the ones that teams want the bq cron
# reporter emails to go to.
TEAM_EMAIL = {
    'infrastructure': 'infrastructure-blackhole@khanacademy.org',
    'classroom': 'coached-perf-reports@khanacademy.org',
    'learning-platform': 'independent-learning-blackhole@khanacademy.org',
    'test-prep': 'testprep-blackhole@khanacademy.org',
    'content-platform': 'content-platform-analytics@khanacademy.org',
    'mpp': 'mpp-blackhole@khanacademy.org',
    'districts': 'coached-perf-reports@khanacademy.org',
    'frontend-infra': 'fe-infrastructure-blackhole@khanacademy.org',
    'architecture': 'infrastructure-blackhole@khanacademy.org',
    'unknown': 'infrastructure-blackhole@khanacademy.org',
}
# TODO(amos): Maybe validate this against the loaded data.
TEAM_IDS = TEAM_EMAIL.keys()

DATA_FILE = 'ownership_data.json'
DAY = 60 * 60 * 24

# Path to gstuil script on Toby, setup by aws-config.
GS_PATH = '~/google-cloud-sdk/bin/gsutil'
GS_DATA = 'gs://webapp-artifacts/ownership_data.json'

_data_cache = None


def email(id):
    return TEAM_EMAIL[id]


def title(id):
    return _load_data()['teams'][id]['readable_name']


def slack_channel(id):
    return _load_data()['teams'][id]['slack_channel']


# From dev/ownership.py
def _owner_by_prefix(owners, name, sep='.'):
    while name:
        owner = owners.get(name)
        if owner is not None:
            return owner

        if sep in name:
            name, _ = name.rsplit(sep, 1)
        else:
            break
    return None


# from dev/ownership.py
def _owner_by_regexps(owners, string):
    for regexps, owner in owners:
        if all(regexp.match(string) for regexp in regexps):
            return owner
    return None


def _refresh_data(path):
    "Reload ownership data from GCS if it's stale."
    if os.path.exists(path):
        if os.path.getmtime(path) > time.time() - DAY:
            # File has already been updated today, don't refresh
            return
    subprocess.check_call([os.path.expanduser(GS_PATH), 'cp', GS_DATA, path])


def _load_data():
    """Load owneship JSON data.

    Loads from:
    - Memory if present
    - Local filesystem if fresher than 24 hours
    - GCS otherwise
    """
    global _data_cache
    if _data_cache:
        return _data_cache
    path = os.path.abspath(os.path.join(os.path.dirname(__file__), DATA_FILE))
    _refresh_data(path)
    with open(path) as f:
        raw_data = json.load(f)
    data = {}
    data['files'] = {path: team_id for path, team_id in raw_data['files']}
    data['urls'] = [([re.compile(pattern) for pattern in patterns], team_id)
                    for patterns, team_id in raw_data['urls']]
    data['queues'] = {queue: teams
                      for queue, teams in raw_data['queues']}
    data['graphql-queries'] = {query: teams
                               for query, teams in raw_data['graphql-queries']}
    data['routes'] = {route: team_id
                      for route, _, team_id in raw_data['server-routes']}
    data['teams'] = {team['id']: team for team in raw_data['teams']}
    _data_cache = data
    return data


def file_owner(path):
    "Owning team id."
    if path.startswith('/'):
        path = path[1:]
    data = _load_data()['files']
    return _owner_by_prefix(data, path, sep='/')


def url_owner(url):
    "Owning team id."
    data = _load_data()['urls']
    return _owner_by_regexps(data, urlparse.urlsplit(url).path)


def route_owners(route):
    "All owning team ids."
    # Based on dev/ownership.py
    parts = route.strip().split(' [')
    route = parts[0]
    data = _load_data()
    routes = data['routes']
    queues = data['queues']
    queries = data['graphql-queries']
    owners = []
    for extra in parts[1:]:
        match = re.match(r'([\w-]+)(\+[\w-]+)*\]', extra)
        # We can have multiple names in an extra in the case of multiple
        # graphql queries, e.g. `getFoo+getBar`.
        if match is None:
            # Most likey a spam route since it doesn't match our spec.
            break
        for name in match.groups():
            if not name:
                continue
            if name.startswith('+'):
                name = name[1:]
            if name in queues:
                owners += queues[name]
            if name in queries:
                owners += queries[name]
        # Otherwise it's probably a HTTP method, ignore it.
    if owners:
        return list(set(owners))

    # We haven't matched on the query or queue so try the route
    if route in routes:
        return [routes[route]]

    return ['unknown']
