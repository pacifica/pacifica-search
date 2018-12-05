import React from 'react';
import {
    FilterBasedAccessor,
    ObjectState,
    FieldContextFactory,
    RangeQuery,
    SearchkitComponent,
    renderComponent,
    Panel
} from 'searchkit';
import PropTypes from 'prop-types';
import DatePicker from 'react-datepicker';
import moment from 'moment';
import _ from 'lodash';

// CSS for the React datepicker
import "react-datepicker/dist/react-datepicker.css";

/**
 * Searchkit filter accessor for date range
 */
class DateRangeAccessor extends FilterBasedAccessor {

    constructor(key, options){
        super(key, options.id);
        this.state = new ObjectState({});
        this.options = options;
        this.options.fieldOptions = this.options.fieldOptions || {type:"embedded"};
        this.options.fieldOptions.field = this.options.field;
        this.fieldContext = FieldContextFactory(this.options.fieldOptions);
    }

    /**
     * Format date string for elasticsearch query
     * @param date
     * @returns {string}
     */
    formatQueryDate(date) {
        return moment(date).format(this.options.queryDateFormat);
    }

    /**
     * Format date string for UI display
     * @param date
     * @returns {string}
     */
    formatDisplayDate(date) {
        return moment(date).format("D MMM YYYY");
    }

    /**
     * Format date string for storing in state, as this is the value used for URL paths
     * @param date
     * @returns {string}
     */
    formatStateDate(date) {
        return moment(date).format("D MMM YYYY");
    }

    buildSharedQuery(query) {
        if (this.state.hasValue()) {
            let val = this.state.getValue();
            let min = this.formatQueryDate(val.startDate);
            let max = this.formatQueryDate(val.endDate);
            let rangeFilter = this.fieldContext.wrapFilter(RangeQuery(this.options.field,{
                gte:min, lte:max
            }));
            let selectedFilter = {
                name:this.translate(this.options.title),
                value:`${this.formatDisplayDate(val.startDate)} - ${this.formatDisplayDate(val.endDate)}`,
                id:this.options.id,
                remove:()=> {
                    this.state = this.state.clear()
                }
            };

            return query
                .addFilter(this.key, rangeFilter)
                .addSelectedFilter(selectedFilter)
        }

        return query
    }

    getBuckets(){
        return this.getAggregations([
            this.key,
            this.fieldContext.getAggregationPath(),
            this.key, "buckets"], [])
    }

    /**
     * This filter does not show the facet categorization, so it will always have a value and should not be disabled
     * @returns {boolean}
     */
    isDisabled() {
        return false;
    }

    /**
     * This is the method to add aggregations to the query, if you want to display your facet aggregations.
     * We aren't going to bin the search results for continuous dates, so we don't add anything to the query.
     * @param query
     * @returns {*}
     */
    buildOwnQuery(query) {
        return query;
    }
}

class DatePickerCustomInput extends React.Component {

    render () {
        return (
            <button
                className={"btn btn-primary btn-sm"}
                onClick={this.props.onClick}>
                {this.props.value}
            </button>
        )
    }
}

DatePickerCustomInput.propTypes = {
    onClick: PropTypes.func,
    value: PropTypes.string
};
DatePickerCustomInput.displayName = "DatePickerCustomInput";

/**
 * Our own Searchkit filter component so we can filter by start date and end date
 */
export default class DateRangeFilter extends SearchkitComponent {

    constructor(props)
    {
        super(props);
    }

    defineAccessor()
    {
        const { id, title, startDate, endDate, queryDateFormat, field, fieldOptions,
            interval, showHistogram } = this.props;
        return new DateRangeAccessor(id,{
            id, startDate, endDate, queryDateFormat, title, field,
            interval, loadHistogram:showHistogram, fieldOptions
        })
    }

    defineBEMBlocks()
    {
        let block = this.props.mod || "sk-date-filter";
        return {
            container: block,
            labels: block + "-value-labels"
        }
    }

    search()
    {
        // TODO: Only search if the dates are valid
        this.searchkit.performSearch();
    }

    startDateChanged(date)
    {
        const state = this.accessor.state.getValue();
        var startDate = this.accessor.formatStateDate(date);
        var endDate = _.get(state, "endDate", this.props.endDate);

        this.accessor.state = this.accessor.state.setValue({startDate: startDate, endDate: endDate});
        this.forceUpdate();
        this.search();
    }

    endDateChanged(date)
    {
        const state = this.accessor.state.getValue();
        var endDate = this.accessor.formatStateDate(date);
        var startDate = _.get(state, "startDate", this.props.startDate);

        this.accessor.state = this.accessor.state.setValue({startDate: startDate, endDate: endDate});
        this.forceUpdate();
        this.search();
    }

    /**
     * Called after the page has been created.
     */
    componentDidMount()
    {
        // The blur is not working in the date picker when used inside searchkit, so
        // I have to hack around it and listen on mousedown :(
        $("html").on('mousedown', (e) =>
        {
            // Determine if we have clicked on a calendar.  If not, then make sure the calendars are closed.
            var datePicker = $(e.target).closest('.react-datepicker');
            if (datePicker.length == 0) {
                this.refs.datePickerStart.setOpen(false);
                this.refs.datePickerEnd.setOpen(false);
            }
        });
    }

    render()
    {
        // assign values from props
        // containerComponent is the parent container
        const { id, title, containerComponent } = this.props;

        return renderComponent(containerComponent, {
            title,
            className: id ? `filter--${id}` : undefined,
            disabled: this.accessor.isDisabled()
        }, this.renderDateComponent())
    }

    renderDateComponent()
    {
        const state = this.accessor.state.getValue();
        var startDate = _.get(state, "startDate", this.props.startDate);
        var endDate = _.get(state, "endDate", this.props.endDate);
        return (
            <div className={"date-picker"}>
                <DatePicker
                    ref="datePickerStart"
                    data-picker-id="date-picker-start"
                    dateFormat="D MMM YYYY"
                    selected={moment(startDate,"D MMM YYYY")}
                    onChange={this.startDateChanged.bind(this)}
                    adjustDateOnChange
                    peekNextMonth
                    showMonthDropdown
                    showYearDropdown
                    dropdownMode="select"
                    customInput={<DatePickerCustomInput />}
                />
                &nbsp;
                &mdash;
                &nbsp;
                <DatePicker
                    ref="datePickerEnd"
                    data-picker-id="date-picker-end"
                    dateFormat="D MMM YYYY"
                    selected={moment(endDate,"D MMM YYYY")}
                    onChange={this.endDateChanged.bind(this)}
                    adjustDateOnChange
                    peekNextMonth
                    showMonthDropdown
                    showYearDropdown
                    dropdownMode="select"
                    customInput={<DatePickerCustomInput />}
                />

            </div>
        )
    }
}

DateRangeFilter.propTypes = _.defaults({
    field: PropTypes.string.isRequired,
    title: PropTypes.string.isRequired,
    id: PropTypes.string.isRequired

}, SearchkitComponent.propTypes);

DateRangeFilter.defaultProps = {
    containerComponent: Panel
};