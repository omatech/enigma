# Changelog

All notable changes to `enigma` will be documented in this file

## 1.0.0 - 2020-03-19

- initial release

## 1.0.1 - 2020-03-20

- fix for orWhere querys

## 1.1.0 - 2020-03-22

- new command to rehydratate the index of given model
- fix where on null indexes found
- support laravel 7

## 1.1.1 - 2020-03-27

- fix encrypt when not blind index is defined

## 1.2.0 - 2020-04-13

- rewrite core
- improved performance using queues to generate indexes
- added methods whereEnigma orWhereEnigma for query builder where eloquent is not present

## 1.2.1 - 2020-04-14

- fix joins on query builder table.column

## 1.2.2 - 2020-04-19

- fix hydration command to use queue

## 1.2.3 - 2021-04-26

- fix eloquent query on joined tables
- improved index for queries
- fix avoid non enigma encryptable field generates a job

## 1.3.0 - 2021-05-12

- support php 8
- support laravel 8

## 1.3.1 - 2021-06-04

- fix query builder on recursive relations

## 1.3.2 - 2021-06-09

- fix query builder on aliased relations

## 1.3.3 - 2021-07-08

- fix null key
- fix long key for mysql

## 1.3.4 - 2021-08-04

- fix models without blindindex
- fix index hydrate when model is softdelete
- update deps

## 1.3.5 - 2022-03-11
 
- fix for artisan commands without app key

## 1.3.6 - 2022-03-22

- support php 8.1
- support laravel 9
