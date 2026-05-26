import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../wayfinder'
export const update = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({ url: update.url(options), method: 'patch' })
update.definition = { methods: ["put","patch"], url: '/user/profile-information' } satisfies RouteDefinition<["put","patch"]>
update.url = (options?: RouteQueryOptions) => update.definition.url + queryParams(options)
update.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({ url: update.url(options), method: 'patch' })
const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'patch'> => ({ action: update.url(options), method: 'patch' })
updateForm.patch = updateForm
update.form = updateForm
const ProfileController = { update }
export default ProfileController
