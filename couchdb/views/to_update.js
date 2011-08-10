/*
 * Displays collections by key, where each key is a source that hasn't been
 * updated yet for that collection.
 * Returns a list of identifier for each artifact in the collection
 */
function(doc) {
    function Synonyms(){
        this.doi = false;
        this.url = false;
        this.pmid = false;
    }
    var sources = [
        "Mendeley",
        "CrossRef",
        "PlosAlm",
        "PlosSearch",
        "Slideshare",
        "Facebook",
        "Icpsr",
        "Dryad"
    ];

    // setup the return object
    ret = {};
    var artifactsCount =  doc.artifact_ids.length;
    for (var i=0; i<artifactsCount; i++ ){
        var artifactId = doc.artifact_ids[i];
        mySynonyms = new Synonyms;
        try {
            var fromCrossRef = doc.sources.CrossRef.artifacts[artifactId];
            mySynonyms.doi = fromCrossRef.doi;
            mySynonyms.url = fromCrossRef.url;
            mySynonyms.pmid = fromCrossRef.pmid;
        }
        catch(e) {}

        ret[artifactId] = mySynonyms;
    }

    // return it only if it's missing sources and is relatively recent
    var sourcesCount = sources.length;
    var key;
    for (i=0; i<sourcesCount; i++) {
        if (!doc.updates[sources[i]]) {
            if (doc.created_at > "1312268700") {           
                emit(sources[i], ret);
            }
        }
    }

    
}