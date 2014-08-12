CHANGELOG
=========

A [BC BREAK] means the update will break the project for many reasons :

* new mandatory configuration
* new dependencies
* class refactoring

### 2014-08-12

* [BC BREAK] create a new 2.3 release to support ZendDiagnostic as LiipMonitor lib is now deprecated

### 2013-02-18

* add Message Admin
* add option to republish messages stored on database

### 2013-04-22

* made the bundle extendable for SonataEasyExtendsBundle. This introduces a [BC BREAK].

### 2013-06-11

* add queues management for doctrine backend

### 2013-06-25

* performance optimizations. This introduces a [BC BREAK].

### 2013-12-13

* MessageManager now extends the DoctrineBaseManager (from SonataCoreBundle).
