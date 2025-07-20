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

  const validateFile = (file: File): string | null => {
    // Vérifier la taille (2MB max)
    const maxSize = 2 * 1024 * 1024; // 2MB
    if (file.size > maxSize) {
      return `Le fichier est trop volumineux. Taille maximale: 2 MB (votre fichier: ${(file.size / (1024 * 1024)).toFixed(2)} MB)`;
    }
    
    // Vérifier le type
    if (file.type && !file.type.startsWith('image/') && !file.type.startsWith('video/')) {
      return 'Type de fichier non supporté. Utilisez des images ou des vidéos.';
    }
    
    return null;
  };

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
    
    // Valider les fichiers
    if (image) {
      const imageError = validateFile(image);
      if (imageError) {
        setError(imageError);
        setLoading(false);
        return;
      }
    }
    
    if (video) {
      const videoError = validateFile(video);
      if (videoError) {
        setError(videoError);
        setLoading(false);
        return;
      }
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
    <div className="glass-form">
      <form onSubmit={handleSubmit}>
        <textarea
          className="glass-input"
          placeholder="Quoi de neuf ?"
          value={texte}
          onChange={e => setTexte(e.target.value)}
          maxLength={500}
          required
          style={{ minHeight: '120px', resize: 'vertical' }}
        />
        <div className="form-actions">
          <div className="media-upload">
            <label className="glass-button">
              📷 Image
              <input type="file" accept="image/*" onChange={e => setImage(e.target.files?.[0] || null)} style={{ display: 'none' }} />
            </label>
            <label className="glass-button">
              🎥 Vidéo
              <input type="file" accept="video/*" onChange={e => setVideo(e.target.files?.[0] || null)} style={{ display: 'none' }} />
            </label>
          </div>
          <div className="upload-info">
            <small style={{ color: 'var(--text-secondary)', fontSize: '0.8rem' }}>
              📏 Taille maximale: 2 MB par fichier
            </small>
          </div>
          <button className="glass-button primary" type="submit" disabled={loading}>
            {loading ? '⏳ Publication...' : '✨ Publier'}
          </button>
        </div>
        <div className="media-preview">
          {image && <img src={URL.createObjectURL(image)} alt="Aperçu" style={{ maxWidth: '200px', borderRadius: '12px', marginTop: '12px' }} />}
          {video && <video src={URL.createObjectURL(video)} controls style={{ maxWidth: '200px', borderRadius: '12px', marginTop: '12px' }} />}
        </div>
        {error && <div className="glass-error">{error}</div>}
        {success && <div className="glass-success">{success}</div>}
      </form>
    </div>
  );
};

export default PublicationForm; 