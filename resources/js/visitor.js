let url, select, mask, track_time, default_number

let prefix = '__trk_'

let data = {
    visit: 0,
    first_visit: 0,
    google_client_id: '',
    metrika_client_id: '',
    amocrm_visitor_uid: '',
    landing_page: '',
    referrer: '',
    phone: {
        number: '',
        ttl: '',
    },

    utm: {
        utm_source: '',
        utm_medium: '',
        utm_campaign: '',
        utm_content: '',
        utm_term: '',
        utm_referrer: '',
    },
}

/**
 *  Init
 * @param {object} options 
 */
export default function init(options) {

    url = options.url
    select = options.select
    mask = options.mask
    default_number = options.default_number
    track_time = options.track_time * 1000

    let search = window.location.search

    data.first_visit = getLocalStorage(`${prefix}first_visit`) || 0
    data.visit = getCookie(`${prefix}visit`) || 0

    if (data.visit == 0) {
        setLocalStorage(`${prefix}referrer`, '')
        setLocalStorage(`${prefix}landing_page`, '')
        setLocalStorage(`${prefix}utm`, '')
    } else {
        setCookie(`${prefix}visit`, data.visit, track_time)
    }

    if (document.referrer && (document.referrer.split('/')[2] != window.location.hostname)) {
        data.referrer = setLocalStorage(`${prefix}referrer`, document.referrer)
        setLocalStorage(`${prefix}utm`, '')
        data.landing_page = setLocalStorage(`${prefix}landing_page`, document.location.hostname + document.location.pathname)
        data.visit = 0
    } else {
        data.referrer = getLocalStorage(`${prefix}referrer`)
        data.landing_page = getLocalStorage(`${prefix}landing_page`) || setLocalStorage(`${prefix}landing_page`, document.location.hostname + document.location.pathname)
    }
    
    let google_client_id = getCookie('_ga')
    let metrika_client_id = getCookie('_ym_uid')
    let amocrm_visitor_uid = getLocalStorage('amocrm_visitor_uid')

    if (google_client_id) {
        data.google_client_id = google_client_id.split('.').slice(-2).join('.')
    }

    if (metrika_client_id) {
        data.metrika_client_id = metrika_client_id
    }

    if (amocrm_visitor_uid) {
        data.amocrm_visitor_uid = amocrm_visitor_uid
    }

    if (search.match(/utm_source=/)) {

        let old_utm = getLocalStorage(`${prefix}utm`) ? JSON.parse(getLocalStorage(`${prefix}utm`)) : {}

        data.utm.utm_source = search.split('utm_source=')[1].split('&')[0]

        if (data.visit && (data.utm.utm_source != old_utm.utm_source) ) data.visit = 0 // ???

        // TODO: Проверка всех значений 

        if (search.match(/utm_medium=/))
            data.utm.utm_medium = search.split('utm_medium=')[1].split('&')[0]
        if (search.match(/utm_campaign=/))
            data.utm.utm_campaign = search.split('utm_campaign=')[1].split('&')[0]
        if (search.match(/utm_content=/))
           data.utm.utm_content = search.split('utm_content=')[1].split('&')[0]
        if (search.match(/utm_term=/))
           data.utm.utm_term = decodeURI(search.split('utm_term=')[1].split('&')[0])
        if (search.match(/utm_referrer=/))
           data.utm.utm_referrer = search.split('utm_referrer=')[1].split('&')[0]

        setLocalStorage(`${prefix}utm`, JSON.stringify(data.utm))
    } else {
        let utm = getLocalStorage(`${prefix}utm`)

        if (utm) {
            data.utm = JSON.parse(utm)
        }
    }

    if (data.visit) {
        intervalCheck()
    } else {
        sendCreate()
    }

    return {
        substitutionNumber,
        getCookie,
        setCookie,
        getLocalStorage,
        setLocalStorage,
        getData,
        sendUpdate,
        sendCreate,
    }
}

/**
 * Замена телефона
 * @param {string} phone 
 */
function substitutionNumber() {

    let number = data.phone.number
                    .replace(/\s{2,}/g, '')
                    .substring(0).replace(/(\d)(\d\d\d)(\d\d\d)(\d\d)(\d\d)/, mask)

    let elements = document.querySelectorAll(select)

    for (let elem of elements) {
        elem.innerHTML = number

        if (elem.tagName === 'A') {
            elem.setAttribute('href', 'tel:+' + data.phone.number)
        }
    }
}

function checkPhoneTTL() {

    if (data.phone.ttl) {
        if (Date.parse(data.phone.ttl) <= Date.now()) {
            data.phone.number = default_number
            data.phone.ttl = false;
            substitutionNumber()
        } 
    }
}

function getCookie(name) {

    let matches = document.cookie.match(new RegExp(
      "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + '=([^;]*)'
    ))
    return matches ? decodeURIComponent(matches[1]) : undefined
}

function setCookie(name, value, time) {

    let str = name + '=' + value

    if (data) {
        str = str + '; expires=' + (new Date(Date.now() + time).toUTCString())
    }

    document.cookie = str
    return value
}

/**
 * Чтение из локального хранилища
 * @param {string} name 
 * @returns {string}
 */
function getLocalStorage(name) {
    return localStorage.getItem(name)
}

/**
 * Запись в локальное хранилище
 * @param {string} name 
 * @param {string} value 
 * @returns {string} 
 */
function setLocalStorage(name, value) {
    localStorage.setItem(name, value)
    return value
}

/**
 * Получить объект данных 
 * @returns {object}
 */
function getData() {
    return data
}

/**
 * Получение асинхронных Данных
 * @returns {bool}
 */
function getAsynсData() {

    if (data.metrika_client_id && data.google_client_id && data.amocrm_visitor_uid) {
        return true
    }

    if (!data.amocrm_visitor_uid) {
        data.amocrm_visitor_uid = getLocalStorage('amocrm_visitor_uid') || ''
    }

    if (!data.google_client_id) {
        let google_client_id = getCookie('_ga')
        if (google_client_id) {
            data.google_client_id = google_client_id.split('.').slice(-2).join('.')
        }
    }

    if (!data.metrika_client_id) {
        let metrika_client_id = getCookie('_ym_uid')
        if (metrika_client_id) {
            data.metrika_client_id = metrika_client_id
        }
    }

	return false
}

/**
 * Проверка данных
 */
function intervalCheck() {
    let checks = 0
    let interval = 50
    let maxTimeout = 3000

    let maxChecks = maxTimeout / interval, 

    t = setInterval(function () {

        if (getAsynсData() || ++checks > maxChecks) {
            sendUpdate()
            clearInterval(t)
        } 
    }, interval)
}

/**
 * Отправка обновлений о визите
 */
function sendUpdate() {

    sendRequest(url+'/update', function (res) {
        if (data.phone != false) {
            data.phone = res.data.phone
        }

        if (data.phone) {
            substitutionNumber()

            if (data.phone.ttl) {
                setTimeout(checkPhoneTTL, (Date.parse(data.phone.ttl) - Date.now()));
            }
        }
    });
}

/** 
 * Отправка данных для создание визита
*/
function sendCreate() {
    
    sendRequest(url+'/create', function(res) {
        data.visit =  res.data.visit
        data.first_visit =  res.data.first_visit
        setCookie(`${prefix}visit`, res.data.visit, track_time)
        setLocalStorage(`${prefix}first_visit`, res.data.first_visit)

        intervalCheck() 

        if (data.phone != false) {
            data.phone = res.data.phone
        }

        if (data.phone) {
            substitutionNumber()
        }
    });
}

/**
 * Отправка данных на сервер
 */
 function sendRequest(url, callback) {

    let xhr = new XMLHttpRequest()
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200 &&
            callback && typeof callback == "function"
        ) {
            try {
                let res = JSON.parse(xhr.responseText)
                callback(res);
            } catch (e) {}
        }
    }
    xhr.open('POST', url, true)
    xhr.setRequestHeader('Content-type', 'application/json;charset=UTF-8')
    xhr.send(JSON.stringify(data))
}