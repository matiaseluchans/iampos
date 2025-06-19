import axios from '@/axios/axios' // o desde '@/axios' si configuraste alias
 


export async function login(email, password) {
  const response = await axios.post('/api/login', { email, password })

  const token = response.data.access_token

  localStorage.setItem('token', token)
  console.log(token);
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

  return response.data.user
}

export async function getUser() {
  try {
    // Verifica si hay token en localStorage
    const token = localStorage.getItem('token')
    
    if (!token) {
      throw new Error('No authentication token found')
    }
    
    // Configura el header para esta petición específica
    const config = {
      headers: {
        Authorization: `Bearer ${token}`
      },
      withCredentials: true // Solo necesario si usas cookies junto con tokens
    }
    
    const response = await axios.get('/api/user', config)

    console.log("response");
    console.log(response);
    
    return response.data
  } catch (error) {
    console.error('Error fetching user data:', error)
    throw error
  }
}
export async function logout() {
  await axios.post('/api/logout')
  localStorage.removeItem('token')
  delete axios.defaults.headers.common['Authorization']
}
