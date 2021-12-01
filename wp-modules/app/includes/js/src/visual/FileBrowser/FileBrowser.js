import React, {useState} from 'react';

export function FileBrowser( props ) {

    const files = props.files;

    function renderFileList() {
        const renderedFiles = [];

        for( const file in files ) {
            renderedFiles.push(
                <div className="file">
                    I AM A FILE
                </div>
            );
        }

        return renderedFiles;
    }

    return(
        <div className="">
            { renderFileList() }
        </div>
    )
}