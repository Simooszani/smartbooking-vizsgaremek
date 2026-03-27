import Vue from 'vue'
import hu from './hu'
import en from './en'

const messages = { hu, en }

export const i18nState = Vue.observable({
  locale: localStorage.getItem('locale') || 'hu'
})

export function t(key) {
  const keys = key.split('.')
  let result = messages[i18nState.locale]
  for (const k of keys) {
    if (result === undefined || result === null) return key
    result = result[k]
  }
  return result !== undefined && result !== null ? result : key
}

export function setLocale(lang) {
  i18nState.locale = lang
  localStorage.setItem('locale', lang)
}

export function getLocale() {
  return i18nState.locale
}

// Global mixin so every component can use this.t() and this.currentLocale
Vue.mixin({
  computed: {
    currentLocale() {
      return i18nState.locale
    }
  },
  methods: {
    t(key) {
      // Access reactive locale to trigger re-render on change
      void i18nState.locale
      return t(key)
    }
  }
})
