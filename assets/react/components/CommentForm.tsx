import React, { useState } from 'react';
import { useFlash } from '../context/FlashContext';
import { useLoader } from '../context/LoaderContext';
import { getToken } from '../api/auth';

const API_URL = '/api/commentaires';

const CommentForm: React.FC<{ publicationId: number; onSuccess?: () => void }> = ({ publicationId, onSuccess }) => {
  const [content, setContent] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const { showFlash } = useFlash();
  const { showLoader, hideLoader } = useLoader();
  const token = getToken();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    showLoader();
    setError(null);
    setSuccess(null);
    if (!token) {
      setError('Vous devez être connecté pour commenter.');
      showFlash('error', 'Vous devez être connecté pour commenter.');
      setLoading(false);
      hideLoader();
      return;
    }
    try {
      const res = await fetch(API_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        credentials: 'include',
        body: JSON.stringify({
          content,
          publication: `/api/publications/${publicationId}`,
        }),
      });
      let data: any = null;
      let text = await res.text();
      try {
        data = JSON.parse(text);
      } catch {
        // Si ce n'est pas du JSON, c'est une erreur serveur HTML
        throw new Error('Erreur serveur : ' + text);
      }
      if (!res.ok) {
        throw new Error(data.error || data.message || 'Erreur lors de l\'envoi du commentaire');
      }
      setSuccess('Commentaire envoyé !');
      showFlash('success', 'Commentaire envoyé !');
      setContent('');
      if (onSuccess) onSuccess();
    } catch (err: any) {
      setError(err.message || 'Erreur inconnue');
      showFlash('error', err.message || 'Erreur inconnue');
    } finally {
      setLoading(false);
      hideLoader();
    }
  };

  return (
    <form className="comment-form" onSubmit={handleSubmit} style={{ marginTop: 8 }}>
      <textarea
        placeholder="Tweeter votre réponse..."
        value={content}
        onChange={e => setContent(e.target.value)}
        required
        disabled={loading}
        style={{ minHeight: 60 }}
      />
      <button type="submit" className="btn" disabled={loading || !content.trim()} style={{ marginTop: 4 }}>
        {loading ? 'Envoi...' : 'Répondre'}
      </button>
      {error && <div style={{ color: 'red', marginTop: 8 }}>{error}</div>}
      {success && <div style={{ color: 'green', marginTop: 8 }}>{success}</div>}
    </form>
  );
};

export default CommentForm; 