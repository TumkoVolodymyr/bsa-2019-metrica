import {
    SET_SELECTED_PERIOD,
    SET_LINE_CHART_DATA,
    SET_LINE_CHART_FETCHING,
    RESET_LINE_CHART_FETCHING, SET_DATA_TYPE
} from "./types/mutations";

export default {
    [SET_SELECTED_PERIOD]: (state, period) => {
        state.selectedPeriod = period;
    },
    [SET_LINE_CHART_DATA]: (state, value) => {
        state.chartData.items = value;
    },
    [SET_DATA_TYPE]: (state, value) => {
        state.dataToFetch = value;
    },
    [SET_LINE_CHART_FETCHING]: (state) => {
        state.chartData.isFetching = true;
    },
    [RESET_LINE_CHART_FETCHING]: (state) => {
        state.chartData.isFetching = false;
    },
};
