<?php
class MendeleyPluginController
{
        /**
         * Returns a JSON string object to the browser when hitting the root of the domain
         *
         * @url GET /
         */
        public function test()
        {
                return "Hello World!!!t!";
        }


        /**
         * Returns metrics values for a Mendeley String ID
         * @url GET /metrics/:id=<string id>
         */
        public function getMetrics($id = null)
        {
                if ($id) {
                        $mendeley = Mendeley::getMetrics($id); // possible metrics loading method
                } else {
                        $mendeley = $_SESSION['mendeley'];
                }

                return $mendeley; // serializes object into JSON
        }

}
?>
