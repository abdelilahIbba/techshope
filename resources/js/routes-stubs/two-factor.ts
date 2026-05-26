import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../wayfinder'
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({ url: show.url(options), method: 'get' })
show.definition = { methods: ["get","head"], url: '/user/two-factor-authentication' } satisfies RouteDefinition<["get","head"]>
show.url = (options?: RouteQueryOptions) => show.definition.url + queryParams(options)
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({ url: show.url(options), method: 'get' })
const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({ action: show.url(options), method: 'get' })
showForm.get = showForm
show.form = showForm
export const enable = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({ url: enable.url(options), method: 'post' })
enable.definition = { methods: ["post"], url: '/user/two-factor-authentication' } satisfies RouteDefinition<["post"]>
enable.url = (options?: RouteQueryOptions) => enable.definition.url + queryParams(options)
enable.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({ url: enable.url(options), method: 'post' })
const enableForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({ action: enable.url(options), method: 'post' })
enableForm.post = enableForm
enable.form = enableForm
export const disable = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({ url: disable.url(options), method: 'delete' })
disable.definition = { methods: ["delete"], url: '/user/two-factor-authentication' } satisfies RouteDefinition<["delete"]>
disable.url = (options?: RouteQueryOptions) => disable.definition.url + queryParams(options)
disable.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({ url: disable.url(options), method: 'delete' })
const disableForm = (options?: RouteQueryOptions): RouteFormDefinition<'delete'> => ({ action: disable.url(options), method: 'delete' })
disableForm.delete = disableForm
disable.form = disableForm
export const confirm = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({ url: confirm.url(options), method: 'post' })
confirm.definition = { methods: ["post"], url: '/user/confirmed-two-factor-authentication' } satisfies RouteDefinition<["post"]>
confirm.url = (options?: RouteQueryOptions) => confirm.definition.url + queryParams(options)
confirm.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({ url: confirm.url(options), method: 'post' })
const confirmForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({ action: confirm.url(options), method: 'post' })
confirmForm.post = confirmForm
confirm.form = confirmForm
export const qrCode = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({ url: qrCode.url(options), method: 'get' })
qrCode.definition = { methods: ["get","head"], url: '/user/two-factor-qr-code' } satisfies RouteDefinition<["get","head"]>
qrCode.url = (options?: RouteQueryOptions) => qrCode.definition.url + queryParams(options)
qrCode.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({ url: qrCode.url(options), method: 'get' })
const qrCodeForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({ action: qrCode.url(options), method: 'get' })
qrCodeForm.get = qrCodeForm
qrCode.form = qrCodeForm
export const secretKey = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({ url: secretKey.url(options), method: 'get' })
secretKey.definition = { methods: ["get","head"], url: '/user/two-factor-secret-key' } satisfies RouteDefinition<["get","head"]>
secretKey.url = (options?: RouteQueryOptions) => secretKey.definition.url + queryParams(options)
secretKey.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({ url: secretKey.url(options), method: 'get' })
const secretKeyForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({ action: secretKey.url(options), method: 'get' })
secretKeyForm.get = secretKeyForm
secretKey.form = secretKeyForm
export const recoveryCodes = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({ url: recoveryCodes.url(options), method: 'get' })
recoveryCodes.definition = { methods: ["get","head"], url: '/user/two-factor-recovery-codes' } satisfies RouteDefinition<["get","head"]>
recoveryCodes.url = (options?: RouteQueryOptions) => recoveryCodes.definition.url + queryParams(options)
recoveryCodes.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({ url: recoveryCodes.url(options), method: 'get' })
const recoveryCodesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({ action: recoveryCodes.url(options), method: 'get' })
recoveryCodesForm.get = recoveryCodesForm
recoveryCodes.form = recoveryCodesForm
export const regenerateRecoveryCodes = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({ url: regenerateRecoveryCodes.url(options), method: 'post' })
regenerateRecoveryCodes.definition = { methods: ["post"], url: '/user/two-factor-recovery-codes' } satisfies RouteDefinition<["post"]>
regenerateRecoveryCodes.url = (options?: RouteQueryOptions) => regenerateRecoveryCodes.definition.url + queryParams(options)
regenerateRecoveryCodes.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({ url: regenerateRecoveryCodes.url(options), method: 'post' })
const regenerateRecoveryCodesForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({ action: regenerateRecoveryCodes.url(options), method: 'post' })
regenerateRecoveryCodesForm.post = regenerateRecoveryCodesForm
regenerateRecoveryCodes.form = regenerateRecoveryCodesForm
export default { show, enable, disable, confirm, qrCode, secretKey, recoveryCodes, regenerateRecoveryCodes }
