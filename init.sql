drop database neulife;
create database neulife;
use neulife;
create table movie (
id              integer auto_increment primary key,
rid             integer unique not null,
name            varchar(256) not null,
type		varchar(50) not null,
category	smallint not null,
sort            varchar(50) not null,
comment         varchar(1000),
auther          varchar(50) not null,
img		varchar(256) not null,
dlw             integer default 0,
dlm             integer default 0,
dla             integer default 0,
tctime          timestamp default current_timestamp,
time            timestamp not null
)ENGINE=InnoDB DEFAULT CHARSET=utf8;



create table movie_item (
id              integer auto_increment primary key,
movie_id        integer not null,
rid             integer unique not null,
name            varchar(50) not null,
status		smallint default 0,
rawsize		integer,
size            integer,
path            varchar(256)
-- 未填充表示未转码完成
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- insert into movie (
-- id,rid,name,type,sort,comment,auther,img,dlw,dlm,dla,time,tctime
-- ) values (
-- 1,3645,'大连东软信息学院2015年迎新晚会','电影','校园活动','大连东软信息学院2015年迎新晚会','逍遥麒麟','test',1000,2000,3000,1131266759,1446626759
-- );

-- insert into movie_item (
-- id,movie_id,rid,name,size,path
-- ) values (
-- 1,1,42558,'大连东软信息学院2015年迎新晚会[本地下载]',3210080603,'201511041703000'
-- );
