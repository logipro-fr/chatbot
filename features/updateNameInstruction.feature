# language: fr

Fonctionnalité: Mettre à jour l'assistant 
Permet de modifier le nom et les instructions d'un assistant existant

  Scénario: Mettre à jour le nom et les instructions d'un chatbot
    Étant donné qu'un chatbot nommé "Assistant based on the context" avec les instructions "Respond like a nextSign salesperson using a formal tone and short sentences." existe
    Quand l'utilisateur met à jour le nom du chatbot en "Assistant" et les instructions en "Vous êtes un assistant très serviable."
    Alors le chatbot doit avoir le nom "Assistant"
    Et le chatbot doit avoir les instructions "Vous êtes un assistant très serviable."


  Scénario: Mettre à jour uniquement le nom du chatbot
    Étant donné qu'un chatbot nommé "Assistant based on the context" avec les instructions "Respond like a nextSign salesperson using a formal tone and short sentences." existe
    Quand l'utilisateur met à jour le nom du chatbot en "Assistant"
    Alors le chatbot doit avoir le nom "Assistant"
    Et le chatbot doit avoir les instructions "Respond like a nextSign salesperson using a formal tone and short sentences."


  Scénario: Mettre à jour uniquement les instructions du chatbot
    Étant donné qu'un chatbot nommé "Assistant based on the context" avec les instructions "Respond like a nextSign salesperson using a formal tone and short sentences." existe
    Quand l'utilisateur met à jour les instructions du chatbot en "Vous êtes un assistant très serviable."
    Alors le chatbot doit avoir le nom "Assistant based on the context"
    Et le chatbot doit avoir les instructions "Vous êtes un assistant très serviable."  