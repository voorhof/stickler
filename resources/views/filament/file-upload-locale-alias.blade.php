{{--
Script for normalizing locale values (supports both `nl_BE` and `nl-BE`) before aliasing to `nl`.
Fix is needed for upload-localization of FilePond locale aliasing in an `nl_BE` setup.
--}}
<script data-navigate-once>
    (() => {
        const localeAliases = {
            nl_be: 'nl',
        }

        const normalizeLocale = (locale) => {
            if (typeof locale !== 'string') {
                return null
            }

            return locale.replace('-', '_').toLowerCase()
        }

        const resolveLocaleAlias = (locale) => {
            const normalizedLocale = normalizeLocale(locale)

            return normalizedLocale ? localeAliases[normalizedLocale] ?? null : null
        }

        const wrapFileUploadFactory = (factory) => {
            if (typeof factory !== 'function') {
                return factory
            }

            if (factory.__hasFileUploadLocaleAliasWrapper) {
                return factory
            }

            const wrappedFactory = (config = {}) => {
                const localeAlias = resolveLocaleAlias(config.locale)

                if (localeAlias) {
                    config.locale = localeAlias
                }

                return factory(config)
            }

            wrappedFactory.__hasFileUploadLocaleAliasWrapper = true

            return wrappedFactory
        }

        const wrapAlpineData = (alpineData) => {
            if (typeof alpineData !== 'function') {
                return alpineData
            }

            if (alpineData.__hasFileUploadLocaleAliasWrapper) {
                return alpineData
            }

            const wrappedData = function (name, callback) {
                if (name === 'fileUploadFormComponent' && typeof callback === 'function') {
                    return alpineData.call(this, name, wrapFileUploadFactory(callback))
                }

                return alpineData.call(this, name, callback)
            }

            wrappedData.__hasFileUploadLocaleAliasWrapper = true

            return wrappedData
        }

        const installWrapper = () => {
            if (typeof window.Alpine?.data === 'function') {
                window.Alpine.data = wrapAlpineData(window.Alpine.data)

                return true
            }

            return false
        }

        if (installWrapper()) {
            return
        }

        let alpineInstance = null

        Object.defineProperty(window, 'Alpine', {
            configurable: true,
            get: () => alpineInstance,
            set: (value) => {
                alpineInstance = value

                if (typeof value?.data === 'function') {
                    value.data = wrapAlpineData(value.data)
                }
            },
        })

        document.addEventListener('livewire:navigated', installWrapper)
    })()
</script>
