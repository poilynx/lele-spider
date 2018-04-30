#! /bin/bash

PROROOT=$(dirname $0)
mkdir -p $PROROOT/catche
mkdir -p $PROROOT/videos
mkdir -p $PROROOT/pictures
mysql -u root -p<init.sql


