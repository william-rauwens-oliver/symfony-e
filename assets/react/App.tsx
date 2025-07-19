import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import FeedPage from './pages/FeedPage';
import SuggestionsPage from './pages/SuggestionsPage';
import ProfilePage from './pages/ProfilePage';
import ProfileEditPage from './pages/ProfileEditPage';
import ProfileDeletePage from './pages/ProfileDeletePage';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import HashtagPage from './pages/HashtagPage';
import SearchPage from './pages/SearchPage';
import LikePage from './pages/LikePage';
import LegalMentionsPage from './pages/LegalMentionsPage';
import PrivacyPolicyPage from './pages/PrivacyPolicyPage';
import Layout from './components/Layout';
import PrivateRoute from './components/PrivateRoute';
import { FlashProvider } from './context/FlashContext';
import FlashMessage from './components/FlashMessage';
import { LoaderProvider } from './context/LoaderContext';
import Loader from './components/Loader';
import { useUser, UserProvider } from './context/UserContext';

// Composant pour rediriger vers le profil de l'utilisateur connecté
const ProfileRedirect: React.FC = () => {
  const { user } = useUser();
  return user ? <Navigate to={`/profile/${user.id}`} replace /> : <Navigate to="/login" replace />;
};

function App() {
  return (
    <UserProvider>
      <LoaderProvider>
        <Loader />
        <FlashProvider>
          <FlashMessage />
          <BrowserRouter>
            <Layout>
              <Routes>
                <Route path="/login" element={<LoginPage />} />
                <Route path="/register" element={<RegisterPage />} />
                <Route path="/hashtag/:tag" element={<HashtagPage />} />
                <Route path="/search" element={<SearchPage />} />
                <Route path="/legal" element={<LegalMentionsPage />} />
                <Route path="/privacy" element={<PrivacyPolicyPage />} />
                <Route
                  path="/"
                  element={
                    <PrivateRoute>
                      <FeedPage />
                    </PrivateRoute>
                  }
                />
                <Route
                  path="/suggested"
                  element={
                    <PrivateRoute>
                      <SuggestionsPage />
                    </PrivateRoute>
                  }
                />
                <Route
                  path="/profile"
                  element={
                    <PrivateRoute>
                      <ProfileRedirect />
                    </PrivateRoute>
                  }
                />
                <Route
                  path="/profile/:id"
                  element={
                    <PrivateRoute>
                      <ProfilePage />
                    </PrivateRoute>
                  }
                />
                <Route
                  path="/profile/username/:username"
                  element={
                    <PrivateRoute>
                      <ProfilePage />
                    </PrivateRoute>
                  }
                />
                <Route
                  path="/profile/edit"
                  element={
                    <PrivateRoute>
                      <ProfileEditPage />
                    </PrivateRoute>
                  }
                />
                <Route
                  path="/profile/delete"
                  element={
                    <PrivateRoute>
                      <ProfileDeletePage />
                    </PrivateRoute>
                  }
                />
                <Route
                  path="/likes"
                  element={
                    <PrivateRoute>
                      <LikePage />
                    </PrivateRoute>
                  }
                />
                <Route path="/debug-profile/:id" element={<ProfilePage />} />
              </Routes>
            </Layout>
          </BrowserRouter>
        </FlashProvider>
      </LoaderProvider>
    </UserProvider>
  );
}

export default App;
