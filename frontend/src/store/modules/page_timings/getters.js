import {
    GET_SELECTED_PERIOD,
    GET_BUTTON_DATA,
    GET_ACTIVE_BUTTON,
    GET_TABLE_DATA_ITEMS,
    GET_TABLE_DATA_FETCHING,
    GET_GROUPED_PARAMETER,
    GET_LINE_CHART_ITEMS,
    GET_LINE_CHART_FETCHING,
    GET_LINE_CHART_DATA,
    FETCH_TABLE_DATA,
} from "./types/getters";

export default {
    [GET_SELECTED_PERIOD]: (state) => state.selectedPeriod,
    [GET_BUTTON_DATA]: (state) => state.buttonData,
    [GET_ACTIVE_BUTTON]: (state) => state.activeButton,
    [GET_TABLE_DATA_ITEMS]: (state) => state.tableData.items,
    [GET_TABLE_DATA_FETCHING]: (state) => state.tableData.isFetching,
    [GET_GROUPED_PARAMETER]: (state) => state.tableData.groupedParameter,
    [GET_LINE_CHART_ITEMS]: (state) => state.chartData.items,
    [GET_LINE_CHART_FETCHING]: (state) => state.chartData.isFetching,
    [GET_LINE_CHART_DATA]: (state) => state.chartData,
    [FETCH_TABLE_DATA]: (state) => state.tableData,
};