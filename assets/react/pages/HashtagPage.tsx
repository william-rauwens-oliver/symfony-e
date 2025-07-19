import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';

const HashtagPage: React.FC = () => {
  const { tag } = useParams<{ tag: string }>();
  const [loading, setLoading] = useState(true);
  const [publications, setPublications] = useState<any[]>([]);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    setLoading(true);
    setError(null);
    fetch(`/api/publications?hashtag=${encodeURIComponent(tag || '')}`)
      .then(res => {
        if (!res.ok) throw new Error('Erreur lors du chargement des publications');
        return res.json();
      })
      .then(data => {
        setPublications(data['hydra:member'] || data);
        setLoading(false);
      })
      .catch(err => {
        setError(err.message);
        setLoading(false);
      });
  }, [tag]);

  return (
    <>
      <div className="main-header">
        <h1>#{tag}</h1>
      </div>
      {loading && <p>Chargement des publications...</p>}
      {error && <p style={{ color: 'red' }}>{error}</p>}
      {!loading && !error && publications.length === 0 && (
        <div className="publication">
          <div className="publication-content">
            <p>Aucune publication pour ce hashtag.</p>
          </div>
        </div>
      )}
      {!loading && !error && publications.map(pub => (
        <article className="publication" key={pub.id}>
          <div className="publication-header">
            <span className="publication-author">{pub.user?.username || 'Utilisateur inconnu'}</span>
            <span className="publication-username">@{pub.user?.username || 'user'}</span>
            <span className="publication-date">· {pub.createdAt ? new Date(pub.createdAt).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }) : 'Date inconnue'}</span>
          </div>
          <div className="publication-content">
            {(pub.content || 'AUCUN CONTENU').split(/(#[\w\d_]+)/g).map((part: string, idx: number) =>
              /^#[\w\d_]+$/.test(part) ? (
                <Link key={idx} to={`/hashtag/${part.substring(1).toLowerCase()}`} className="hashtag">{part}</Link>
              ) : (
                part
              )
            )}
            {pub.image && (
              <div className="publication-media">
                <img src={`/uploads/images/${pub.image}`} alt="Image" style={{ maxWidth: '100%', borderRadius: 12, marginTop: 10 }} />
              </div>
            )}
            {pub.video && (
              <div className="publication-media">
                <video controls style={{ maxWidth: '100%', borderRadius: 12, marginTop: 10 }}>
                  <source src={`/uploads/videos/${pub.video}`} type="video/mp4" />
                  Votre navigateur ne supporte pas la vidéo.
                </video>
              </div>
            )}
          </div>
        </article>
      ))}
    </>
  );
};

export default HashtagPage; 