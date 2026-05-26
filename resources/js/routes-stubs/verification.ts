import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../wayfinder'
export const send = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({ url: send.url(options), method: 'post' })
send.definition = { methods: ["post"], url: '/email/verification-notification' } satisfies RouteDefinition<["post"]>
send.url = (options?: RouteQueryOptions) => send.definition.url + queryParams(options)
send.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({ url: send.url(options), method: 'post' })
const sendForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({ action: send.url(options), method: 'post' })
sendForm.post = sendForm
send.form = sendForm
export default { send }
