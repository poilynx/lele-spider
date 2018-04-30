#! /bin/bash

PROROOT=$(dirname $0)
rm $PROROOT/catche/*
rm $PROROOT/videos/*
rm $PROROOT/pictures/*
mysql -u root -p<init.sql


