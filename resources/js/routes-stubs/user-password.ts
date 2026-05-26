import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../wayfinder'
export const edit = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({ url: edit.url(options), method: 'patch' })
edit.definition = { methods: ["put","patch"], url: '/user/password' } satisfies RouteDefinition<["put","patch"]>
edit.url = (options?: RouteQueryOptions) => edit.definition.url + queryParams(options)
edit.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({ url: edit.url(options), method: 'patch' })
const editForm = (options?: RouteQueryOptions): RouteFormDefinition<'patch'> => ({ action: edit.url(options), method: 'patch' })
editForm.patch = editForm
edit.form = editForm
export default { edit }
