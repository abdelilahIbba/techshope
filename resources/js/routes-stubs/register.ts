import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../wayfinder'
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({ url: store.url(options), method: 'post' })
store.definition = { methods: ["post"], url: '/register' } satisfies RouteDefinition<["post"]>
store.url = (options?: RouteQueryOptions) => store.definition.url + queryParams(options)
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({ url: store.url(options), method: 'post' })
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({ action: store.url(options), method: 'post' })
storeForm.post = storeForm
store.form = storeForm
export default { store }
