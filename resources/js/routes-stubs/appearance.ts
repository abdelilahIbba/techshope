import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../wayfinder'
export const edit = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({ url: edit.url(options), method: 'get' })
edit.definition = { methods: ["get","head"], url: '/settings/appearance' } satisfies RouteDefinition<["get","head"]>
edit.url = (options?: RouteQueryOptions) => edit.definition.url + queryParams(options)
edit.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({ url: edit.url(options), method: 'get' })
const editForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({ action: edit.url(options), method: 'get' })
editForm.get = editForm
edit.form = editForm
export default { edit }
