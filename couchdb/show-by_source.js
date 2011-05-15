/* 
 * This can be found at "total-impact/_design/main/_show/by_source/".
 * It reorganizes the contents of the document for more useful display.
 */


function(doc, req) {
    var artifacts = [];
    var ret = '<ul>';
    
    // a list of artifacts
    for (source in doc.sources) {
        for (artifact in doc.sources[source]) {
            artifacts.push(doc.sources[source][artifact]);
        }
    }
    
    for (id in artifacts) {
        ret += '<li>' + artifacts[id] + '</li>';
    }
    
    ret += '</ul>';
    return ret;
}
