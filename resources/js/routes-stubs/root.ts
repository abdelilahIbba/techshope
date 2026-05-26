import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../wayfinder'
export const login = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({ url: login.url(options), method: 'get' })
login.definition = { methods: ["get","head"], url: '/login' } satisfies RouteDefinition<["get","head"]>
login.url = (options?: RouteQueryOptions) => login.definition.url + queryParams(options)
login.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({ url: login.url(options), method: 'get' })
const loginForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({ action: login.url(options), method: 'get' })
loginForm.get = loginForm
login.form = loginForm
export const logout = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({ url: logout.url(options), method: 'post' })
logout.definition = { methods: ["post"], url: '/logout' } satisfies RouteDefinition<["post"]>
logout.url = (options?: RouteQueryOptions) => logout.definition.url + queryParams(options)
logout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({ url: logout.url(options), method: 'post' })
const logoutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({ action: logout.url(options), method: 'post' })
logoutForm.post = logoutForm
logout.form = logoutForm
export const home = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({ url: home.url(options), method: 'get' })
home.definition = { methods: ["get","head"], url: '/' } satisfies RouteDefinition<["get","head"]>
home.url = (options?: RouteQueryOptions) => home.definition.url + queryParams(options)
home.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({ url: home.url(options), method: 'get' })
const homeForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({ action: home.url(options), method: 'get' })
homeForm.get = homeForm
home.form = homeForm
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({ url: dashboard.url(options), method: 'get' })
dashboard.definition = { methods: ["get","head"], url: '/dashboard' } satisfies RouteDefinition<["get","head"]>
dashboard.url = (options?: RouteQueryOptions) => dashboard.definition.url + queryParams(options)
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({ url: dashboard.url(options), method: 'get' })
const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({ action: dashboard.url(options), method: 'get' })
dashboardForm.get = dashboardForm
dashboard.form = dashboardForm
export const register = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({ url: register.url(options), method: 'get' })
register.definition = { methods: ["get","head"], url: '/register' } satisfies RouteDefinition<["get","head"]>
register.url = (options?: RouteQueryOptions) => register.definition.url + queryParams(options)
register.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({ url: register.url(options), method: 'get' })
const registerForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({ action: register.url(options), method: 'get' })
registerForm.get = registerForm
register.form = registerForm
export default { login, logout, home, dashboard, register }

