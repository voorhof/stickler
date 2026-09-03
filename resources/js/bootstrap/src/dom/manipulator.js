/**
 * --------------------------------------------------------------------------
 * dom/manipulator.js
 * --------------------------------------------------------------------------
 */

function normalizeData(value) {
    if (value === 'true') {
        return true
    }

    if (value === 'false') {
        return false
    }

    if (value === Number(value).toString()) {
        return Number(value)
    }

    if (value === '' || value === 'null') {
        return null
    }

    if (typeof value !== 'string') {
        return value
    }

    try {
        return JSON.parse(decodeURIComponent(value))
    } catch {
        return value
    }
}

function normalizeDataKey(key) {
    return key.replace(/[A-Z]/g, chr => `-${chr.toLowerCase()}`)
}

const Manipulator = {
    setDataAttribute(element, key, value) {
        element.setAttribute(`data-${normalizeDataKey(key)}`, value)
    },

    removeDataAttribute(element, key) {
        element.removeAttribute(`data-${normalizeDataKey(key)}`)
    },

    getDataAttributes(element) {
        if (!element) {
            return {}
        }

        const attributes = {}
        const ppKeys = Object.keys(element.dataset).filter(key => key.startsWith('pp') && !key.startsWith('ppConfig'))

        for (const key of ppKeys) {
            let pureKey = key.replace(/^pp/, '')
            pureKey = pureKey.charAt(0).toLowerCase() + pureKey.slice(1)
            attributes[pureKey] = normalizeData(element.dataset[key])
        }

        return attributes
    },

    getDataAttribute(element, key) {
        return normalizeData(element.getAttribute(`data-${normalizeDataKey(key)}`))
    }
}

export default Manipulator
