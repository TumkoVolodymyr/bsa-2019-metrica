import {
    CHANGE_SELECTED_PERIOD,
    CHANGE_GROUPED_PARAMETER,
    CHANGE_ACTIVE_BUTTON,
    CHANGE_FETCHED_BUTTON_STATE,
    CHANGE_FETCHED_TABLE_STATE,
    GET_CHART_DATA
} from "./types/actions";
import {
    SET_SELECTED_PERIOD,
    SET_GROUPED_PARAMETER,
    SET_ACTIVE_BUTTON,
    RESET_BUTTON_FETCHING,
    SET_BUTTON_FETCHING,
    RESET_TABLE_FETCHING,
    SET_TABLE_FETCHING,
    //SET_CHART_DATA
} from "./types/mutations";
import {getTimeByPeriod} from "../../../services/periodService";
import {newVisitorsService} from "../../../api/visitors/newVisitorsService";

export default {
    [CHANGE_SELECTED_PERIOD]: (context, payload) => {
        context.commit(SET_SELECTED_PERIOD, payload.value);
    },
    [CHANGE_ACTIVE_BUTTON]: (context, button) => {
        context.commit(SET_ACTIVE_BUTTON, button);
    },
    [CHANGE_FETCHED_BUTTON_STATE]: (context, data) => {

        if (data.value) {
            context.commit(SET_BUTTON_FETCHING, data.button);
        } else {
            context.commit(RESET_BUTTON_FETCHING, data.button);
        }
    },
    [CHANGE_GROUPED_PARAMETER]: (context, parameter) => {
        context.commit(SET_GROUPED_PARAMETER, parameter);
    },
    [CHANGE_FETCHED_TABLE_STATE]: (context, value) => {

        if (value) {
            context.commit(SET_TABLE_FETCHING);
        } else {
            context.commit(RESET_TABLE_FETCHING);
        }
    },
    [GET_CHART_DATA]: (context) => {
        const period = getTimeByPeriod(context.state.selectedPeriod);
        const startDate = period.startDate;
        const endDate = period.endDate;
        return newVisitorsService.fetchButtonValue(startDate, endDate)
            .then(response => {
                return response;
            }).catch((response) => {
                return Promise.reject(response);
            });
        //return context.state.selectedPeriod;
        //context.commit(SET_CHART_DATA);
    },
};
