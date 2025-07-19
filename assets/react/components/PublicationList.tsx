import React, { useEffect, useState } from 'react';
import { fetchPublications } from '../api/publication';
import LikeButton from './LikeButton';
import CommentList from './CommentList';
import CommentForm from './CommentForm';
import RepostButton from './RepostButton';
import ShareButton from './ShareButton';
import './Publication.css';
import { useUser } from '../context/UserContext';

interface PublicationListProps {
  userId?: number;
  refresh?: boolean;
}

const PublicationList: React.FC<PublicationListProps> = ({ userId, refresh }) => {
  console.log('PublicationList mounted');
  const [publications, setPublications] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const { user } = useUser();

  const refreshFn = async () => {
    setLoading(true);
    setError('');
    try {
      const data = await fetchPublications();
      setPublications(data);
    } catch (err) {
      setError("Erreur lors du chargement des publications");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { refreshFn(); }, [userId, refresh]);

  // Trie les publications par date décroissante (plus récentes en haut)
  const sortedPublications = [...publications].sort((a, b) => {
    const dateA = new Date(a.createdAt).getTime();
    const dateB = new Date(b.createdAt).getTime();
    return dateB - dateA;
  });

  console.log('DEBUG publications:', publications);

  return (
    <div className="publication-list">
      {loading && <div className="publication-loader">Chargement...</div>}
      {error && <div className="publication-error">{error}</div>}
      {!loading && !error && publications.length === 0 && (
        <div className="publication-empty">Aucune publication à afficher.</div>
      )}
      {sortedPublications.map(pub => (
        <div className="publication-card" key={pub.id}>
          <div className="publication-header">
            <img className="publication-avatar" src={pub.user?.avatar || '/default-avatar.png'} alt="avatar" />
            <div className="publication-meta">
              <span className="publication-username">{pub.user?.username || 'Utilisateur'}</span>
              <span className="publication-date">{new Date(pub.createdAt).toLocaleString()}</span>
            </div>
          </div>
          <div className="publication-content">{pub.texte}</div>
          {pub.image && <img src={pub.image} alt="media" className="publication-image" />}
          {pub.video && <video src={pub.video} controls className="publication-video" />}
          <div className="publication-actions">
            <LikeButton 
              publicationId={pub.id} 
              initialCount={pub.likeCount || 0}
              initiallyLiked={Array.isArray(pub.likes) && user ? pub.likes.some((like: any) => like.user === `/api/users/${user.id}`) : false}
              onLike={refreshFn}
            />
            {/* Nouvelle box de commentaires complète */}
            <div className="publication-comments-box">
              <CommentForm publicationId={pub.id} onSuccess={refreshFn} />
              <CommentList publicationId={pub.id} />
            </div>
            <RepostButton publicationId={pub.id} />
            <ShareButton publicationId={pub.id} />
          </div>
        </div>
      ))}
    </div>
  );
};

export default PublicationList; 