/*
 * Reformats a collection to show metrics by artifact by artifact_type.
 * Artifacts with no metrics are discarded, since we've no way of determining
 *  their type.
 */
function(doc, req) {
    var meta = {};
    var artifacts = {};

    // make the meta section: data that apply to the whole collection
    meta = doc;
    meta.about_sources = {};
    meta.errors = {};


    // make the artifacts section: an array of artifacts, each with its activity
    //  from various Sources
    for (var sourceName in doc.sources){
        meta.about_sources[sourceName] = doc.sources[sourceName].about

        // add artifact names to the artifacts object, then put metrics for that
        //     artifact-source combo under the artifact.sourceName
        for (var artifactName in doc.sources[sourceName].artifacts){
            if (typeof artifacts[artifactName] == "undefined") {
                artifacts[artifactName] = {};
            }
            artifacts[artifactName][sourceName] = doc.sources[sourceName].artifacts[artifactName];


        }
    }

    delete meta.sources;
    delete meta._rev;
    delete meta._revisions;

    var ret = {
        'meta': meta,
        'artifacts': artifacts
    }

    return toJSON(ret);
}