import React from 'react';
/**
 * Utility class to wrap a collapsible panel around facets
 */
export default class CollapsiblePanel extends React.Component {
    constructor(props) {
        super(props);
        // set state collapsed = true
        this.state = {collapsed: true};

        // This binding is necessary to make `this` work in the callback
        this.toggleDisplay = this.toggleDisplay.bind(this);
    }

    toggleDisplay()
    {
        // toggle the value of the collapsed state variable - this will trigger a render any time state changes
        this.setState(prevState => ({
            collapsed: !prevState.collapsed
        }));
    }

    render() {
        // if collapsed, then use display:none on the panel
        let classname;
        if(this.state.collapsed) {
            classname = "collapsible-panel collapsed";
        } else {
            classname = "collapsible-panel expanded";
        }
        return (
            <div className={classname}>
                <div className={"collapsible-title"} onClick={this.toggleDisplay}>{this.props.title}</div>
                <div className={"collapsible-body"}>
                    {this.props.children}
                </div>
            </div>
        );
    }
}