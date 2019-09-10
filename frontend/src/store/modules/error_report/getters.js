import {
    GET_SELECTED_PERIOD,
    GET_LINE_CHART_DATA,
    GET_FORMAT_LINE_CHART_DATA,
    GET_TABLE_DATA,
    GET_TABLE_DATA_FETCHING,
    GET_LINE_CHART_FETCHING,
} from "./types/getters";

import { chartDataTransformer } from "@/api/widgets/transformers";

export default {
    [GET_SELECTED_PERIOD]: (state) => state.selectedPeriod,
    [GET_LINE_CHART_DATA]: (state) => state.chartData,
    [GET_FORMAT_LINE_CHART_DATA]: (state) => {
        return chartDataTransformer(state.chartData.items, state.selectedPeriod);
    },
    [GET_TABLE_DATA]: (state) => state.tableData,
    [GET_TABLE_DATA_FETCHING]: (state) => state.tableData.isFetching,
    [GET_LINE_CHART_FETCHING]: (state) => state.chartData.isFetching,
};
