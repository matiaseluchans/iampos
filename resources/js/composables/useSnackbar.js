import { ref } from 'vue'

export function useSnackbar() {
  const snackbar = ref(false)
  const snackbarMessage = ref('')
  const snackbarColor = ref('success')

  const showMessage = (message, color = 'success') => {
    snackbarMessage.value = message
    snackbarColor.value = color
    snackbar.value = true
  }

  const showSuccess = (message) => showMessage(message, 'success')
  const showError = (message) => showMessage(message, 'error')
  const showWarning = (message) => showMessage(message, 'warning')
  const showInfo = (message) => showMessage(message, 'info')

  return {
    snackbar,
    snackbarMessage,
    snackbarColor,
    showSuccess,
    showError,
    showWarning,
    showInfo
  }
}