export async function login(email: string, password: string) {
  console.log('DEBUG LOGIN - Starting login request for:', email);
  // Nettoyage du localStorage avant chaque login
  localStorage.removeItem('jwt_token');
  try {
    const res = await fetch('/api/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password }),
    });
    console.log('DEBUG LOGIN - Response status:', res.status);
    if (!res.ok) {
      let error = 'Identifiants invalides';
      try { error = (await res.json()).error || error; } catch {}
      console.log('DEBUG LOGIN - Error response:', error);
      throw new Error(error);
    }
    const data = await res.json();
    console.log('DEBUG LOGIN RESPONSE', data);
    if (data.token) {
      console.log('DEBUG LOGIN - Storing token in localStorage');
      localStorage.setItem('jwt_token', data.token);
      console.log('DEBUG LOGIN - Token stored, localStorage now contains:', localStorage.getItem('jwt_token') ? 'TOKEN' : 'NO TOKEN');
    } else {
      console.log('DEBUG LOGIN - No token in response');
    }
    // Log du contenu du localStorage après login
    console.log('DEBUG LOGIN - localStorage after login:', localStorage.getItem('jwt_token'));
    return data;
  } catch (err) {
    console.error('DEBUG LOGIN - Fetch error:', err);
    throw err;
  }
}

export async function register(username: string, email: string, password: string) {
  const res = await fetch('/api/register', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username, email, password }),
  });
  if (!res.ok) throw new Error('Erreur lors de l\'inscription');
  return await res.json();
}

export function logout() {
  console.log('DEBUG LOGOUT - Removing token from localStorage');
  localStorage.removeItem('jwt_token');
}

export function getToken() {
  const token = localStorage.getItem('jwt_token');
  console.log('DEBUG GET TOKEN - Token found:', token ? 'YES' : 'NO');
  return token;
}

export async function getCurrentUser() {
  console.log('DEBUG GET CURRENT USER - Starting');
  const token = getToken();
  if (!token) {
    console.log('DEBUG GET CURRENT USER - No token found');
    return null;
  }
  
  try {
    console.log('DEBUG GET CURRENT USER - Making request to /api/me');
    const res = await fetch('/api/me', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    });
    console.log('DEBUG GET CURRENT USER - Response status:', res.status);
    
    if (res.status === 401) {
      console.log('DEBUG GET CURRENT USER - 401 Unauthorized, removing token');
      localStorage.removeItem('jwt_token');
      return null;
    }
    
    if (!res.ok) {
      console.log('DEBUG GET CURRENT USER - Response not ok:', res.status);
      return null;
    }
    
    const userData = await res.json();
    console.log('DEBUG GET CURRENT USER - User data received:', userData);
    return userData;
  } catch (error) {
    console.error('DEBUG GET CURRENT USER - Error:', error);
    return null;
  }
}

// Utilitaire pour fetch avec JWT
export async function fetchWithAuth(url: string, options: RequestInit = {}) {
  const token = getToken();
  const headers: Record<string, string> = {};
  
  if (token) {
    headers.Authorization = `Bearer ${token}`;
  }
  
  // Ne pas ajouter Content-Type pour FormData, laissez le navigateur le gérer
  if (!(options.body instanceof FormData)) {
    headers['Content-Type'] = 'application/json';
  }
  
  // Fusionner les headers avec ceux fournis dans options
  const finalHeaders = { ...headers, ...options.headers };
  
  console.log('🔧 fetchWithAuth - URL:', url);
  console.log('🔧 fetchWithAuth - Method:', options.method || 'GET');
  console.log('🔧 fetchWithAuth - Headers:', finalHeaders);
  console.log('🔧 fetchWithAuth - Body type:', options.body instanceof FormData ? 'FormData' : typeof options.body);
  
  return fetch(url, { ...options, headers: finalHeaders });
} 