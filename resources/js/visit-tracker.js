import Visitor from './visitor';

let visit = Visitor({
    default_number: process.env.CALL_TRACKER_DEFAULT_NUMBER,
    url: process.env.APP_URL + '/api/site/visitor',
    select: `.${process.env.CALL_TRACKER_CSS_CLASS}`,
    mask: process.env.CALL_TRACKER_MASK,
    track_time: process.env.CALL_TRACKER_TRACK_TIME,
})

window.VISIT = visit