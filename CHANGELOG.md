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
