console.log('DEBUG USER CONTEXT - File loaded');

import React, { createContext, useContext, useEffect, useState } from 'react';
import { getCurrentUser, login as apiLogin, logout as apiLogout, register as apiRegister, getToken } from '../api/auth';

export interface User {
  id: number;
  username: string;
  email: string;
}

interface UserContextType {
  user: User | null;
  loading: boolean;
  error: string | null;
  token: string | null;
  login: (email: string, password: string) => Promise<void>;
  logout: () => void;
  register: (username: string, email: string, password: string) => Promise<void>;
  refresh: () => void;
}

const UserContext = createContext<UserContextType>({
  user: null,
  loading: false,
  error: null,
  token: null,
  login: async () => {},
  logout: () => {},
  register: async () => {},
  refresh: () => {},
});

export const useUser = () => useContext(UserContext);

export const UserProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  console.log('DEBUG USER CONTEXT - UserProvider component rendered');
  
  const [user, setUser] = useState<User | null>(null);
  // Initialiser loading à true pour chaque montage
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [token, setToken] = useState<string | null>(() => getToken());

  const fetchUser = async () => {
    console.log('DEBUG USER CONTEXT - fetchUser called');
    setLoading(true);
    setError(null);
    try {
      console.log('DEBUG USER CONTEXT - About to call getCurrentUser');
      const u = await getCurrentUser();
      console.log('DEBUG USER CONTEXT - getCurrentUser result:', u);
      setUser(u);
      setToken(getToken()); // <-- MAJ du token à chaque fetchUser
      console.log('DEBUG USER CONTEXT - User state updated to:', u);
    } catch (err: any) {
      console.log('DEBUG USER CONTEXT - getCurrentUser error:', err);
      console.log('DEBUG USER CONTEXT - Error details:', err.message, err.stack);
      setUser(null);
      setToken(null); // <-- reset token si erreur
      setError(err.message || 'Erreur inconnue');
    } finally {
      // On log l'état juste avant de passer loading à false
      setTimeout(() => {
        console.log('DEBUG USER CONTEXT - fetchUser finally block, setting loading to false. user:', user, 'token:', token);
        setLoading(false);
      }, 0);
    }
  };

  useEffect(() => {
    console.log('DEBUG USER CONTEXT - Initial useEffect');
    setLoading(true); // <-- Toujours forcer loading à true au montage
    fetchUser();
  }, []);

  const login = async (email: string, password: string) => {
    console.log('DEBUG USER CONTEXT - login called for:', email);
    setLoading(true);
    setError(null);
    try {
      console.log('DEBUG USER CONTEXT - About to call apiLogin');
      const result = await apiLogin(email, password);
      console.log('DEBUG USER CONTEXT - apiLogin result:', result);
      
      // Attendre un peu pour s'assurer que le token est stocké
      console.log('DEBUG USER CONTEXT - Waiting 100ms before fetchUser');
      await new Promise(resolve => setTimeout(resolve, 100));
      
      setToken(getToken()); // <-- MAJ du token après login
      console.log('DEBUG USER CONTEXT - About to call fetchUser');
      // Récupérer les informations utilisateur
      await fetchUser();
      console.log('DEBUG USER CONTEXT - fetchUser completed');
    } catch (err: any) {
      console.log('DEBUG USER CONTEXT - login error:', err);
      console.log('DEBUG USER CONTEXT - Login error details:', err.message, err.stack);
      setError(err.message || 'Erreur inconnue');
      setToken(null); // <-- reset token si erreur
      throw err;
    } finally {
      console.log('DEBUG USER CONTEXT - Login finally block, setting loading to false');
      setLoading(false);
    }
  };

  const logout = () => {
    console.log('DEBUG USER CONTEXT - logout called');
    apiLogout();
    setUser(null);
    setToken(null); // <-- reset token après logout
    setError(null);
  };

  const register = async (username: string, email: string, password: string) => {
    console.log('DEBUG USER CONTEXT - register called for:', email);
    setLoading(true);
    setError(null);
    try {
      await apiRegister(username, email, password);
    } catch (err: any) {
      console.log('DEBUG USER CONTEXT - register error:', err);
      setError(err.message || 'Erreur inconnue');
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const refresh = () => {
    console.log('DEBUG USER CONTEXT - refresh called');
    fetchUser();
  };

  return (
    <UserContext.Provider value={{ user, loading, error, token, login, logout, register, refresh }}>
      {children}
    </UserContext.Provider>
  );
}; 