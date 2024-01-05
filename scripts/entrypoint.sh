#!/bin/bash

########################################
# Exit as soon as any line in the bash script fails.
set -o errexit

# pipefail: the return value of a pipeline is the status of the last command to exit with a non-zero status, or zero if no command exited with a non-zero status
set -o pipefail

bash /tmp/docker_install.sh

exec "$@"
