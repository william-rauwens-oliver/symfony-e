import React from 'react';
import { useLoader } from '../context/LoaderContext';

const Loader: React.FC = () => {
  const { isLoading } = useLoader();
  if (!isLoading) return null;
  return (
    <div style={{
      position: 'fixed',
      top: 0,
      left: 0,
      width: '100vw',
      height: '100vh',
      background: 'rgba(255,255,255,0.6)',
      zIndex: 3000,
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
    }}>
      <div style={{
        width: 64,
        height: 64,
        border: '6px solid #1da1f2',
        borderTop: '6px solid #fff',
        borderRadius: '50%',
        animation: 'spin 1s linear infinite',
      }} />
      <style>{`
        @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }
      `}</style>
    </div>
  );
};

export default Loader; 