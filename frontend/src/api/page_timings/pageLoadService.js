import requestService from "@/services/requestService";
import config from "@/config";
import {chartTransformerToSeconds, buttonTransformerToSeconds, tableTransformerPageTiming} from '../transformers';
import _ from "lodash";

const resourceUrl = config.getApiUrl();

const chartDataUrl = '/page-timing/chart/page-loading';
const btnDataUrl = '/page-timing/button/page-loading';
const tableDataUrl = '/page-timing/table/page-loading';
const errorMessage = 'Something went wrong with getting average page loading time';


const fetchButtonValue = (startDate, endDate) => {
    return requestService.get(resourceUrl + btnDataUrl, {}, {
        'filter[startDate]': startDate,
        'filter[endDate]': endDate
    }).then(response => buttonTransformerToSeconds(response.data))
        .catch(error => Promise.reject(
            new Error(
                _.get(
                    error,
                    'response.data.error.message',
                    errorMessage
                )
            )
        ));
};

const fetchChartValues = (startDate, endDate, interval) => {
    return requestService.get(resourceUrl + chartDataUrl, {}, {
        'filter[startDate]': startDate,
        'filter[endDate]': endDate,
        'filter[period]': interval
    }).then(response => response.data.map(chartTransformerToSeconds))
        .catch(error => Promise.reject(
            new Error(
                _.get(
                    error,
                    'response.data.error.message',
                    errorMessage
                )
            )
        ));
};

const fetchTableValues = (startDate, endDate, parameter) => {
    return requestService.get(resourceUrl + tableDataUrl, {}, {
        'filter[startDate]': startDate,
        'filter[endDate]': endDate,
        'filter[parameter]': parameter
    }).then(response => response.data.map(tableTransformerPageTiming))
        .catch(error => Promise.reject(
            new Error(
                _.get(
                    error,
                    'response.data.error.message',
                    errorMessage
                )
            )
        ));
};

export const pageLoadService = {
    fetchChartValues,
    fetchButtonValue,
    fetchTableValues
};
