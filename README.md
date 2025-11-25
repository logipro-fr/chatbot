# Chatbot

Chatbot is a Conversations server based on LLMs. The user asks a question, he gets an answer. Simple isn’t it!

# Requirements

* docker >=24.0.6
* git
* ChatGPT API key

# Install

```console
git clone https://github.com/logipro-fr/chatbot.git
```

```console
cd chatbot
./install
```

## Add .env.local files

Variable environemment 

* HOST_IP
* CHATBOT_KEY_API

MS Windows dev uwsing WSL must consitder to add:

```
HOST_IP=0.0.0.0
```

