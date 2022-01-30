#!/bin/bash

rm -rf /var/run/httpd || true
mkdir -p /var/run/httpd || true

/usr/sbin/httpd -k start -DFOREGROUND