# language: fr

Fonctionnalité: Détacher un assistant de fichier
Permet de détacher un assistant d'un fichier spécifique sans le supprimer

    Scénario: Detacher un fichier d'un asssitant existant
        Étant donné qu'un assistant avec l'id "ast_abc123" est attaché au fichier "file-document.pdf"
        Quand l'utilisateur détache l'assistant du fichier "document.pdf"
        Alors l'assistant "ast_abc123" ne doit plus être attaché au fichier "document.pdf"