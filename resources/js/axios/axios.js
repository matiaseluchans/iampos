import axios from 'axios'

const instance = axios.create({
  baseURL: 'http://iampos.com', // tu backend Laravel
})

const token = localStorage.getItem('token')
if (token) {
  instance.defaults.headers.common['Authorization'] = `Bearer ${token}`
}

export default instance
