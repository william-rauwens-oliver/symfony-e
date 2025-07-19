import React, { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import Layout from '../components/Layout';
import '../pages/Search.css';

interface SearchResult {
  publications: any[];
  users: any[];
  hashtags: string[];
  total: number;
  query: string;
}

// Ajout de logs globaux
console.log('🔵 SearchPage chargé');

const SearchPage: React.FC = () => {
  const [searchParams, setSearchParams] = useSearchParams();
  const [results, setResults] = useState<SearchResult>({
    publications: [],
    users: [],
    hashtags: [],
    total: 0,
    query: ''
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [query, setQuery] = useState(searchParams.get('q') || '');

  // Log de l'état initial
  useEffect(() => {
    console.log('🟢 useEffect (init) - query:', query, 'searchParams:', searchParams.toString());
  }, []);

  // Recherche automatique au chargement si un paramètre q est présent
  useEffect(() => {
    const urlQuery = searchParams.get('q');
    console.log('🚀 Initialisation de la page avec query URL:', urlQuery);
    if (urlQuery && urlQuery !== query) {
      console.log('🔄 Mise à jour du query depuis l\'URL');
      setQuery(urlQuery);
    }
  }, [searchParams]);

  // Log des résultats quand ils changent
  useEffect(() => {
    console.log('📊 État actuel des résultats:', results);
  }, [results]);

  const performSearch = async (searchQuery: string) => {
    if (!searchQuery.trim()) {
      setResults({
        publications: [],
        users: [],
        hashtags: [],
        total: 0,
        query: ''
      });
      console.log('🔸 Recherche ignorée (query vide)');
      return;
    }

    setLoading(true);
    setError(null);

    console.log('🔍 Début de la recherche pour:', searchQuery);

    try {
      console.log('📡 Appel API: /api/search?q=' + encodeURIComponent(searchQuery));
      const response = await fetch(`/api/search?q=${encodeURIComponent(searchQuery)}`);
      const text = await response.text();
      let data;
      try {
        data = JSON.parse(text);
      } catch (parseErr) {
        setError('Erreur de parsing JSON: ' + (parseErr instanceof Error ? parseErr.message : parseErr));
        setResults({ publications: [], users: [], hashtags: [], total: 0, query: searchQuery });
        return;
      }
      if (!response.ok) {
        setError('Erreur API: ' + (data && data.error ? data.error : response.status));
        setResults({ publications: [], users: [], hashtags: [], total: 0, query: searchQuery });
        return;
      }
      setResults(data);
      console.log('✅ Données reçues:', data);
    } catch (err) {
      console.error('💥 Erreur complète:', err);
      setError('Erreur réseau: ' + (err instanceof Error ? err.message : 'Erreur inconnue'));
      setResults({
        publications: [],
        users: [],
        hashtags: [],
        total: 0,
        query: searchQuery
      });
    } finally {
      setLoading(false);
      console.log('🔚 Fin de recherche');
    }
  };

  useEffect(() => {
    console.log('🔄 useEffect déclenché avec query:', query);
    if (query) {
      performSearch(query);
    }
  }, [query]);

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    setSearchParams({ q: query });
    console.log('🟠 handleSearch déclenché avec:', query);
  };

  const handleUserClick = (userId: number) => {
    console.log('🟣 handleUserClick:', userId);
    window.location.href = `/profile/${userId}`;
  };

  const handleHashtagClick = (hashtag: string) => {
    console.log('🟣 handleHashtagClick:', hashtag);
    window.location.href = `/hashtag/${hashtag}`;
  };

  return (
    <div className="search-container">
      <div className="search-header">
        <h1>🔍 Recherche</h1>
        <form onSubmit={handleSearch} className="search-form">
          <input
            type="text"
            value={query}
            onChange={(e) => {
              setQuery(e.target.value);
              console.log('🟡 Saisie utilisateur:', e.target.value);
            }}
            placeholder="Rechercher utilisateurs, hashtags, publications..."
            className="search-input"
          />
          <button type="submit" className="search-btn">
            Rechercher
          </button>
        </form>
      </div>

      {loading && (
        <div className="loading">
          <p>Recherche en cours...</p>
        </div>
      )}

      {error && (
        <div className="error">
          <p>❌ {error}</p>
        </div>
      )}

      {!loading && !error && query && (
        <div className="search-results">
          <h2>Résultats pour "{query}"</h2>
          {results.total === 0 ? (
            <div className="no-results">
              <p>Aucun résultat trouvé pour "{query}".</p>
            </div>
          ) : (
            <div className="results-summary">
              <p>{results.total} résultat(s) trouvé(s)</p>
            </div>
          )}

          {/* Section Utilisateurs */}
          {results.users.length > 0 && (
            <div className="search-section">
              <h3>👥 Utilisateurs ({results.users.length})</h3>
              <div className="users-list">
                {results.users.map((user) => (
                  <div key={user.id} className="user-card" onClick={() => handleUserClick(user.id)}>
                    <div className="user-info">
                      <div className="user-avatar">
                        <img src="/default-avatar.png" alt="Avatar" onError={(e) => {
                          (e.target as HTMLImageElement).src = '/default-avatar.png';
                        }} />
                      </div>
                      <div className="user-details">
                        <strong>{user.username}</strong>
                        <span className="user-email">{user.email}</span>
                      </div>
                    </div>
                    <button className="view-profile-btn">Voir le profil</button>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Section Hashtags */}
          {results.hashtags.length > 0 && (
            <div className="search-section">
              <h3>🏷️ Hashtags ({results.hashtags.length})</h3>
              <div className="hashtags-list">
                {results.hashtags.map((hashtag, index) => (
                  <button
                    key={index}
                    className="hashtag-card"
                    onClick={() => handleHashtagClick(hashtag)}
                  >
                    <span className="hashtag-icon">#</span>
                    <span className="hashtag-name">{hashtag}</span>
                  </button>
                ))}
              </div>
            </div>
          )}

          {/* Section Publications */}
          {results.publications.length > 0 && (
            <div className="search-section">
              <h3>📝 Publications ({results.publications.length})</h3>
              <div className="publications-list">
                {results.publications.map((publication) => (
                  <div key={publication.id} className="publication-card">
                    <div className="publication-header">
                      <div className="author-info">
                        <strong>{publication.user?.username || 'Utilisateur inconnu'}</strong>
                        <span className="date">
                          {new Date(publication.createdAt).toLocaleDateString('fr-FR')}
                        </span>
                      </div>
                    </div>
                    <div className="publication-content">
                      {publication.texte || 'Aucun contenu'}
                    </div>
                    <div className="publication-stats">
                      <span>❤️ {publication.likes?.length || 0} likes</span>
                      <span>💬 {publication.commentaires?.length || 0} commentaires</span>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      )}

      {!query && (
        <div className="search-placeholder">
          <p>Entrez un terme de recherche pour commencer.</p>
        </div>
      )}
    </div>
  );
};

export default SearchPage; 