import React, {Component} from 'react';

export default class TransactionListItem extends Component {
    constructor(props) {
        super(props);
        this.displayFileList = this.displayFileList.bind(this);
        this.state = {
            truncate: true
        };

        this.toggleAbstract = this.toggleAbstract.bind(this);
    }

    loadFiles() {
        const source = this.props.result._source;
        $('#'+source.obj_id+'.results_filetree').fancytree({
            checkbox: function(event, data) {
                // Hide checkboxes for folders
                // return data.node.isFolder() ? false : true;

                // Hide all checkboxes for the moment
                return false;
            },
            source: {
                url: '/file_tree/transactions/' + source.obj_id.split('_')[1] + '/files',
                cache: false
            },
        });
    }

    buildSourceObj() {
        const source = this.props.result._source;
        /**
         * source object should be
         * [
         *  {
         *    "title": "proposal #"
         *    "key": "id"
         *    "folder": true
         *    children: [
         *      {
         *          title: "instrument info"
         *          key: "instrument id"
         *          folder: true
         *          children: [
         *              {
         *                  Title: "files uploaded blah blah (transaction number)"
         *                  key: "transaction id"
         *                  folder: true
         *                  lazy: true
         *                  }
         *                  ]
         *                  }
         *                  ]
         */
        const fileTreeObj = {
            title: "Files uploaded (Transaction " + source.obj_id.split('_')[1]+")",
            key: source.obj_id,
            folder: true,
            lazy: true
        };
        return fileTreeObj;
    }
    
    displayFileList() {
        this.openFilePanel();
        this.loadFiles();
    }

    openFilePanel() {
        const source = this.props.result._source;
        $('#'+source.obj_id+'.filesPanel').show();
    }

    toggleAbstract() {
        this.setState({truncate: !this.state.truncate});
    }

    renderAbstract(abstractText) {
        return (
            <p>
                <p className={this.state.truncate ? 'truncate-abstract': ''}>
                    <b>Abstract:</b> {abstractText}
                </p>
                {this.state.truncate ? (
                    <div onClick={this.toggleAbstract} style={{'color':'#08c'}}>Display full abstract</div>
                ) : (
                    <div onClick={this.toggleAbstract} style={{'color':'#08c'}}>Display truncated abstract</div>
                )}
            </p>
        )
    }

    render() {
        const source = this.props.result._source;
        const proposals = source.proposals[0];
        const instruments = source.instruments[0];
        const themes = source.science_themes[0];
        const users = source.users[0];
        return(
            <div className="transactionResultHit">
                <div>
                    <b>Dataset:</b> {source.obj_id.split('_')[1]}
                </div>
                <p>
                    <b>Proposal:</b> {proposals.title} (#{proposals.obj_id.split('_')[1]}) <br />
                    {this.renderAbstract(proposals.abstract)}
                </p>
                <p>
                    <b>Instrument:</b> {instruments.display_name} (#{instruments.obj_id.split('_')[1]}) <br />
                    <b>Insturment Long Name:</b> {instruments.long_name}
                </p>
                <button onClick={this.displayFileList}>View Files</button>
                <div className="transactionFileResults">
                    <div id={source.obj_id} className="filesPanel">
                        <div id={source.obj_id} className="results_filetree"></div>
                    </div>
                </div>
            </div>
        );
    }
}