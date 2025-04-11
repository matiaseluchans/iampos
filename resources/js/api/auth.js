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
  const response = await axios.get('/api/user') // o '/api/user' si usás prefijo
  return response.data
}

export async function logout() {
  await axios.post('/api/logout')
  localStorage.removeItem('token')
  delete axios.defaults.headers.common['Authorization']
}
