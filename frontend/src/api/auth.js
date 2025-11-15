import http from './api'

/**
 * login
 * @param {string} email
 * @param {string} password
 * @param {route name get from laravel base one api url} /v1/auth/login
 * @returns
 * @api import http from './api' URL path http://127.0.0.1:8000/api/
 */

export function userLogin(email, password) {
  const user = {
    email,
    password
  }
  return http.post('/v1/auth/login', user)
}