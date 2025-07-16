// store/index.js
import { createStore } from 'vuex'

//const ADMIN_ROLES = ["bebidas-admin", "petshop-admin", "super-admin"]
import { ADMIN_ROLES } from '@/config/constants';

export default createStore({
  state: {
    user: null,
    token: localStorage.getItem('token') || null
  },
  mutations: {
    SET_USER(state, user) {
      state.user = user
      localStorage.setItem('user', JSON.stringify(user))
    },
    CLEAR_USER(state) {
      state.user = null
      localStorage.removeItem('user')
      localStorage.removeItem('token')
    },
    SET_TOKEN(state, token) {
      state.token = token
      localStorage.setItem('token', token)
    }
  },
  actions: {
    async fetchUser({ commit }) {
      try {
        const user = await getUser() // Tu función existente
        commit('SET_USER', user)
        return user
      } catch (error) {
        commit('CLEAR_USER')
        throw error
      }
    }
  },
  getters: {
    isAuthenticated: state => !!state.token,
    currentUser: state => state.user,
    userPermissions: state => {
      if (!state.user?.roles) return []

      return state.user.roles.flatMap(role => role.permissions.map(p => p.name))
    },

    isAdmin: state => {            
      if (!state.user?.data.roles) return false
      const userRoles = state.user.data.roles

      return ADMIN_ROLES.some(r => userRoles.includes(r))
    },
  }
})
