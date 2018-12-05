import React from 'react';
import ReactDOM from 'react-dom';
import * as Searchkit from 'searchkit';
import TransactionListItem from './transactionListItem';
import DateRangeFilter from './dateRangeFilter';
import CollapsiblePanel from './collapsiblePanel';
import moment from 'moment';

export default class SearchApplication extends React.Component {

    constructor(props) {
        super(props);

        const host = this.getHost(props.esHost);
        this.searchkit = new Searchkit.SearchkitManager(host);

        this.searchkit.translateFunction = (key)=> {
            return {"pagination.next":"Next Page", "pagination.previous":"Previous Page"}[key]
        };

        this.searchkit.addDefaultQuery(this.getDefaultQuery());
    }
    
    getHost(host) {
        return host;
    }

    formatDateForDatePicker(date) {
        // convert to proper format for date picker component
        return moment(date).format("D MMM YYYY");
    }

    getOneYearFromToday() {
        return new Date(new Date().setFullYear(new Date().getFullYear() + 1));
    }

    getDefaultQuery() {
        const BoolMust = Searchkit.BoolMust;
        const TermQuery = Searchkit.TermQuery;
        const MatchQuery = Searchkit.MatchQuery;
        const FilteredQuery = Searchkit.FilteredQuery;

        const instance = this;
        return (query)=> {
            return query.addQuery( BoolMust([
                    TermQuery("_type", "transactions"),
                ])
            )}

    }

    componentDidUpdate () {
        // put code in here that happens after the component is refreshed
    }

    formatDateForElasticSearch(date) {
        // convert to proper format for search_date queries
        return moment(date).format("YYYY-MM-DDTHH:MM:SS");
    }

    decayingScoreQuery(scoreFunction, field, scale, origin, decay, query) {
        if(origin) {
            return {
                function_score: {
                    query: query,

                }
            }
        } else {
            return {
                function_score: {
                    query: query,

                }
            }
        }
    }

    render() {
        var informationText = 'To search multiple terms at once, insert "AND" between them. If a term contains a space, place the term in quotes';
        var TermQuery = Searchkit.TermQuery;
        return(
            <div>
                <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" />
                <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
                <Searchkit.SearchkitProvider searchkit={this.searchkit}>
                    <Searchkit.Layout size="1">
                        <Searchkit.TopBar>
                            <Searchkit.SearchBox
                                translations={{"searchbox.placeholder":"Search Datasets"}}
                                queryOptions={{"minimum_should_match":"95%"}}
                                queryBuilder={Searchkit.QueryString}
                                auotfocus={true}
                                searchOnChange={true}
                                queryFields={["_all"]}
                            />
                            <i
                            className="fas fa-info-circle fa-2x"
                            style={{marginLeft: '10px', marginTop: '5px', color: 'white'}}
                            datatoggle="tooltip"
                            title={informationText}
                            />
                        </Searchkit.TopBar>
                        <Searchkit.LayoutBody>
                            {/* Facets/Filters */}
                            <Searchkit.SideBar>
                                <CollapsiblePanel title="Dataset">
                                    <Searchkit.RefinementListFilter
                                        id="release_data"
                                        title="Is Released"
                                        field="release"
                                        operator="AND"
                                        translations={{"true": "Released Data", "false": "Proprietary Data"}}
                                    />

                                    <DateRangeFilter
                                        id="created_date"
                                        field="created_date"
                                        queryDateFormat="YYYY-MM-DDTHH:MM:SS"
                                        title="Upload Date"
                                        startDate={"1 Jan 2010"}
                                        endDate={this.formatDateForDatePicker(this.getOneYearFromToday())}
                                    />
                                </CollapsiblePanel>

                                <CollapsiblePanel title="Institution">
                                    <Searchkit.RefinementListFilter
                                        id="institution"
                                        title="Institution Name"
                                        field="institutions.keyword"
                                        operator="AND"
                                        size={10}
                                    />
                                </CollapsiblePanel>
                                <CollapsiblePanel title="Instruments" >
                                    <Searchkit.RefinementListFilter
                                        id="instruments"
                                        title="Instruments Name"
                                        field="instruments.keyword"
                                        operator="AND"
                                        size={10}
                                    />
                                </CollapsiblePanel>
                                <CollapsiblePanel title="Instrument Groups" >
                                    <Searchkit.RefinementListFilter
                                        id="instrument_groups"
                                        title="Group Name"
                                        field="instrument_groups.keyword"
                                        operator="AND"
                                        size={10}
                                    />
                                </CollapsiblePanel>
                                <CollapsiblePanel title="Users" >
                                    <Searchkit.RefinementListFilter
                                        id="users"
                                        title="User Name"
                                        field="users.keyword"
                                        operator="AND"
                                        size={10}
                                    />
                                </CollapsiblePanel>
                                <CollapsiblePanel title="Proposals" >
                                    <DateRangeFilter
                                        id="proposals.actual_start_date"
                                        field="proposals.actual_start_date"
                                        queryDateFormat="YYYY-MM-DD"
                                        title="Start Date"
                                        startDate={"1 Jan 2002"}
                                        endDate={this.formatDateForDatePicker(this.getOneYearFromToday())}
                                    />
                                    <DateRangeFilter
                                        id="proposals.actual_end_date"
                                        field="proposals.actual_end_date"
                                        queryDateFormat="YYYY-MM-DD"
                                        title="End Date"
                                        startDate={"1 Jan 2002"}
                                        endDate={this.formatDateForDatePicker(this.getOneYearFromToday())}
                                    />
                                    <Searchkit.RefinementListFilter
                                        id="proposals"
                                        title="Proposal Title"
                                        field="proposals.keyword"
                                        operator="AND"
                                        size={10}
                                    />
                                </CollapsiblePanel>
                            </Searchkit.SideBar>
                            <Searchkit.LayoutResults>
                                <Searchkit.ActionBarRow>
                                    <Searchkit.HitsStats translations={{"hitstats.results_found":"{hitCount} results found"}}/>
                                </Searchkit.ActionBarRow>
                                <Searchkit.ActionBarRow>
                                    <Searchkit.SelectedFilters
                                        translations={{
                                            "End Date":"Proposal End Date",
                                            "Start Date":"Proposal Start Date",
                                            "Upload Date":"Dataset Upload Date",
                                            "User Name":"Dataset Author"
                                        }}
                                    />
                                    <Searchkit.ResetFilters />
                                </Searchkit.ActionBarRow>
                                <Searchkit.Hits
                                    hitsPerPage={15}
                                    itemComponent={TransactionListItem}
                                    scrollTo="body"
                                />
                                <Searchkit.Pagination showNumbers={true} />
                            </Searchkit.LayoutResults>
                        </Searchkit.LayoutBody>
                    </Searchkit.Layout>
                </Searchkit.SearchkitProvider>
            </div>
        );
    }
}

window.startSearchApp = function(esHost) {
    ReactDOM.render(<SearchApplication esHost={esHost} {...this.props} />, document.getElementById('searchkit_section'));
};