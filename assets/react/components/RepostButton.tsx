import React, { useEffect, useState } from 'react';
import { repostPublication, fetchRepostsByUser, deleteRepost } from '../api/publication';
import { useUser } from '../context/UserContext';
import IconRetweet from '../assets/IconRetweet.svg';

interface Props {
  publicationId: number;
}

const RepostButton: React.FC<Props> = ({ publicationId }) => {
  const { user, token } = useUser();
  const [reposted, setReposted] = useState(false);
  const [repostId, setRepostId] = useState<number | null>(null);
  const [loading, setLoading] = useState(false);

  // Vérifie si l'utilisateur a déjà reposté cette publication
  useEffect(() => {
    if (!user) return;
    fetchRepostsByUser(user.id).then(reposts => {
      const found = reposts.find((r: any) => r.publication?.id === publicationId);
      if (found) {
        setReposted(true);
        setRepostId(found.id);
      } else {
        setReposted(false);
        setRepostId(null);
      }
    });
  }, [user, publicationId]);

  const handleRepost = async () => {
    if (!token) return;
    setLoading(true);
    try {
      if (reposted && repostId) {
        await deleteRepost(repostId, token);
        setReposted(false);
        setRepostId(null);
      } else {
        const res = await repostPublication(publicationId, token);
        setReposted(true);
        setRepostId(res.id);
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <button
      className={`repost-btn${reposted ? ' reposted' : ''}`}
      onClick={handleRepost}
      disabled={loading}
      title={reposted ? 'Annuler le retweet' : 'Retweeter'}
      style={reposted ? { background: '#e3f2fd', color: '#1da1f2', border: '1px solid #1da1f2' } : {}}
    >
      <img src={IconRetweet} alt="Reposter" className="repost-icon" />
      {reposted ? 'Annuler le retweet' : 'Retweeter'}
    </button>
  );
};

export default RepostButton; 