import http from 'k6/http';
import { check, sleep } from 'k6';

// export const options = { vus: 1, iterations: 3 };
export const options = {
    stages: [
        { duration: '30s', target: 50 },   
        { duration: '1m',  target: 50 },   
        { duration: '15s', target: 0 },    
    ],
    thresholds: {
        http_req_duration: ['p(95)<500'],  
    },
    summaryTrendStats: ['avg', 'med', 'p(90)', 'p(95)', 'p(99)', 'max'],
};

const BASE = 'http://nginx';
const TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYXBpL3YxL2F1dGgvbG9naW4iLCJpYXQiOjE3OTQzNzkwNTYsImV4cCI6MTc5NDM4NjI1NiwibmJmIjoxNzk0Mzc5MDU2LCJqdGkiOiJ3ckJWb1pwQjl2UXlLTXNWIiwic3ViIjoiOCIsInBydiI6IjAwYmVjMDEwYzEzYjAxODY1NDBlYTk2YjI3YzFkYzEwOGI1OGQyNDAiLCJyb2xlIjoiYWRtaW4ifQ.s974G1zU2YDp3fQTAMo2i0VX3xH2N0pmFOBId93k0to';

export default function () {
    const params = {
        headers: {
            'Authorization': `Bearer ${TOKEN}`,
            'Accept': 'application/json',
        },
    };

    const r1 = http.get(`${BASE}/api/v1/products`, params);
    if (r1.status !== 200) {
    console.error(`status=${r1.status} body=${String(r1.body).substring(0, 150)}`);
    }
    check(r1, { 'first is 200': (r) => r.status === 200 });

    const params2 = {
        headers: {
            'Authorization': `Bearer ${TOKEN}`,
            'Accept': 'application/json',
            'If-None-Match': r1.headers['Etag'] || r1.headers['ETag'] || '',
        },
    };
    const r2 = http.get(`${BASE}/api/v1/products`, params2);
    check(r2, { 'second is 304': (r) => r.status === 304 });

    sleep(0.2);

}