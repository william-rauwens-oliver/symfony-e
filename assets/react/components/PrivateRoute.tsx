import React from 'react';
import { Navigate } from 'react-router-dom';
import { useUser } from '../context/UserContext';

interface PrivateRouteProps {
  children: React.ReactNode;
}

const PrivateRoute: React.FC<PrivateRouteProps> = ({ children }) => {
  const { user, loading } = useUser();
  
  console.log('PrivateRoute - user:', user, 'loading:', loading);
  
  if (loading) {
    console.log('PrivateRoute - still loading...');
    return <div>Chargement...</div>;
  }
  
  if (!user) {
    console.log('PrivateRoute - no user, redirecting to login');
    return <Navigate to="/login" replace />;
  }
  
  console.log('PrivateRoute - user authenticated, rendering children');
  return <>{children}</>;
};

export default PrivateRoute; 