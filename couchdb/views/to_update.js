/*
 * Displays collections by key and by time of last update
 * Returns a list of identifier for each artifact in the collection
 */
function(doc) {
    function Synonyms(){
        this.doi = false;
        this.url = false;
        this.pmid = false;
    }
    ret = {};
    var artifactsCount =  doc.artifact_ids.length;
    for (var i = 0; i < artifactsCount; i++ ){
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
    emit(doc._id, ret);
    
}