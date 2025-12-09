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

* HOST_IP → Set an environment variable to access Swagger or the database
* CHATBOT_KEY_API → Access key for the OpenAI API

For test integration : UpdateAssistantFileControllerTest 
* OPENAI_ASSISTANT_ID → Id to external_Assistant to OpenIA API

MS Windows dev uwsing WSL must consitder to add:

```
HOST_IP=0.0.0.0
```

