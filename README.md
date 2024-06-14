# Chatbot

Chatbot is a project to answer the user question

# Install

```console
git clone git@github.com:logipro-fr/chatbot.git
```

# To Contribute to Datamaps

## Requirements

* docker
* git


## Unit test

```console
bin/phpunit
```

Using Test-Driven Development (TDD) principles (thanks to Kent Beck and others), following good practices (thanks to Uncle Bob and others) and the great book 'DDD in PHP' by C. Buenosvinos, C. Soronellas, K. Akbary

## Manual tests

```console
./start
```
have a local look at http://172.17.0.1:10180/ in your navigator

```console
./stop
```

## Quality

* phpcs PSR12
* phpstan level 9
* coverage 80%
* infection MSI >96%

Quick check with:
```console
./codecheck
```

Check coverage with:
```console
bin/phpunit --coverage-html var
```
and view 'var/index.html' with your browser

Check infection with:
```console
bin/infection
```
and view 'var/infection.html' with your browser
