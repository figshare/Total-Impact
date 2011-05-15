/*
 * Reformats a collection to show metrics by artifact by artifact_type.
 * Artifacts with no metrics are discarded, since we've no way of determining
 *  their type.
 */
function(doc, req) {
    
    // make a denormalised list of artifacts
    var multiArtifacts = []
    for (source in doc.sources) {
        for (artifact in doc.sources[source]) {
            multiArtifacts.push(doc.sources[source][artifact]);
        }
    }
    
    // make a list of metrics
    var metrics = [];
    for (artifact in multiArtifacts){
        var thisMetricsList = multiArtifacts[artifact].list;
        for (i in thisMetricsList){
            metrics.push(thisMetricsList[i]);
        }
    }
    
    // make a list of unique artifacts, each with all its metrics beneath it
    var artifacts = {};
    for (i in metrics) {
        var thisMetric = metrics[i];
        if (typeof(artifacts[thisMetric.id]) == 'undefined') {
            artifacts[thisMetric.id] = {};
        }
        if (thisMetric.metric_value > 0) { // only keep metrics with nonzero values
            var fullMetricName = thisMetric.source_name + ' ' + thisMetric.metric_name;
            artifacts[thisMetric.id][fullMetricName] = thisMetric;
        }
    }
    
    // make a list of artifact types, and place artifacts underneath each type.
    // discard artifacts that don't have metrics
    var artifactTypes = {};
    for (artifactID in artifacts) {
        var thisArtifact = artifacts[artifactID];
        var thisType = null;
        
        // get the type of the artifact, if it has one
        for (metric in thisArtifact){
            // the type of the last metric is assumed to be the type of that artifact...
            thisType = thisArtifact[metric].type;
        }
        
        if (thisType){
            // add the article's type to the list
            if (typeof(artifactTypes[thisType]) == 'undefined') {
                artifactTypes[thisType] = {};
            }

            // add the artifact to the list under the correct type
            artifactTypes[thisType][artifactID] = thisArtifact;
        }
    }    
    
    return toJSON(artifactTypes);
}