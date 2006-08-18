#!/bin/sh
#
# Shell wrapper for symfony (based on Phing shell wrapper)
# $Id$
#
# This script will do the following:
# - check for PHP_COMMAND env, if found, use it.
#   - if not found assume php is on the path
# - check for SYMFONY_HOME env, if found use it
#   - if not look for it
# - check for PHP_CLASSPATH, if found use it
#   - if not found set it using SYMFONY_HOME/lib

SYMFONY_HOME="/users/joesimms/projects/symfony/joesimms/branch/bin"
PHP_CLASSPATH="/users/joesimms/projects/symfony/joesimms/branch/lib"
export PHP_CLASSPATH

if (test -z "$PHP_COMMAND") ; then
  # echo "WARNING: PHP_COMMAND environment not set. (Assuming php on PATH)"
  export PHP_COMMAND=php
fi

$PHP_COMMAND -d html_errors=off -qC $SYMFONY_HOME/symfony.php $*
