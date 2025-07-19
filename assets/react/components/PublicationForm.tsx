import React, { useState } from 'react';
import { createPublication } from '../api/publication';
import { useUser } from '../context/UserContext';
import './Publication.css';

interface Props {
  onPublish: () => void;
}

const PublicationForm: React.FC<Props> = ({ onPublish }) => {
  const { user, token } = useUser();
  const [texte, setTexte] = useState('');
  const [image, setImage] = useState<File | null>(null);
  const [video, setVideo] = useState<File | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  if (!user) return null;

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    setSuccess('');
    if (!texte.trim()) {
      setError('Le texte ne doit pas être vide.');
      setLoading(false);
      return;
    }
    try {
      const formData = new FormData();
      formData.append('texte', texte.trim());
      if (image) formData.append('image', image);
      if (video) formData.append('video', video);
      await createPublication(formData, token!);
      setTexte(''); setImage(null); setVideo(null);
      setSuccess('Publication postée avec succès !');
      setTimeout(() => setSuccess(''), 3000);
      onPublish();
    } catch (err: any) {
      // Gestion d'erreur détaillée
      if (err.response && err.response.data && err.response.data.violations) {
        const violation = err.response.data.violations[0];
        setError(violation.message || 'Erreur lors de la publication');
      } else if (err.response && err.response.data && err.response.data.detail) {
        setError(err.response.data.detail);
      } else {
        setError('Erreur lors de la publication');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <form className="publication-form" onSubmit={handleSubmit}>
      <textarea
        className="publication-textarea"
        placeholder="Quoi de neuf ?"
        value={texte}
        onChange={e => setTexte(e.target.value)}
        maxLength={500}
        required
      />
      <div className="publication-media-inputs">
        <label className="publication-media-label">
          Image
          <input type="file" accept="image/*" onChange={e => setImage(e.target.files?.[0] || null)} />
        </label>
        <label className="publication-media-label">
          Vidéo
          <input type="file" accept="video/*" onChange={e => setVideo(e.target.files?.[0] || null)} />
        </label>
      </div>
      <div className="publication-media-preview">
        {image && <img src={URL.createObjectURL(image)} alt="Aperçu" className="publication-image-preview" />}
        {video && <video src={URL.createObjectURL(video)} controls className="publication-video-preview" />}
      </div>
      <button className="publication-submit-btn" type="submit" disabled={loading}>
        {loading ? 'Publication...' : 'Publier'}
      </button>
      {error && <div className="publication-error">{error}</div>}
      {success && <div className="publication-success">{success}</div>}
    </form>
  );
};

export default PublicationForm; 