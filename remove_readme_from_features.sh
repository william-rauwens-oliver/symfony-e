#!/bin/bash

# Script pour supprimer README.md de toutes les branches feature
# Basé sur main et supprime le README de chaque branche feature

echo "🚀 Suppression du README.md de toutes les branches feature..."

# Liste des branches feature
branches=(
    "feature/authentication-module"
    "feature/buildforms"
    "feature/controllers-services"
    "feature/database-entities"
    "feature/design-system"
    "feature/documentation"
    "feature/feed-module"
    "feature/frontend-react"
    "feature/like-repost-system"
    "feature/profile-module"
    "feature/publication-comment-system"
    "feature/routing-middleware"
    "feature/search-hashtag"
    "feature/security-jwt"
    "feature/suggestion-algorithm"
    "feature/tests"
)

# Aller sur main
git checkout main

# Créer chaque branche feature et supprimer le README
for branch in "${branches[@]}"; do
    echo "📝 Traitement de la branche: $branch"
    
    # Créer la branche à partir de main
    git checkout -b "$branch"
    
    # Supprimer le README
    rm -f README.md
    
    # Commiter la suppression
    git add -A
    git commit -m "Remove README.md from $branch"
    
    # Pousser la branche
    git push origin "$branch"
    
    echo "✅ $branch traitée"
done

# Retourner sur main
git checkout main

echo "🎉 Toutes les branches feature ont été traitées !" 