# Chatbot

Chatbot is a project to answer the user question

# Install

If you have SSH Key use 
```console
git clone git@github.com:logipro-fr/chatbot.git
```
else use

```console
git clone https://github.com/logipro-fr/chatbot.git
```

```console
cd chatbot
./install
```

# To Contribute to Chatbot

## Requirements

* docker >=24.0.6
* git
*ChatGPT API key


## Unit test

```console
bin/phpunit
```

Using Test-Driven Development (TDD) principles (thanks to Kent Beck and others), following good practices (thanks to Uncle Bob and others) and the great book 'DDD in PHP' by C. Buenosvinos, C. Soronellas, K. Akbary

## Integration test

```console
bin/phpunit-integration
```

## Manual tests

```console
./start
```
have a local look at http://172.17.0.1:11080/ in your navigator

```console
./stop
```

## Quality

* phpcs PSR12
* phpstan level 9
* 100% coverage obtained naturally thanks to the “classic school” TDD approach
* we hunt mutants with “Infection”. We aim for an MSI score of 100% for “panache”

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
