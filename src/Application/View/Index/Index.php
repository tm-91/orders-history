<?php $this->renderHeader(); ?>
        <main class="rwd-layout-width rwd-layout-container">
            <section class="history rwd-layout-col-12">
                <?php 
                echo 'HISTORIA EDYCJI</br></br>';
                echo 'ilość wpisów: ' . count($historyEntries);
                ?>
                <div class="history_wrapper">
                    <div class="history_container">
                        <?php
                        foreach ($historyEntries as $entry){
                            /*$view = new \Application\View\View(
                                'Index/History/historyEntry',
                                [
                                    'entry' => $entry,
                                    'translations' => $translations
                                ],
                                $this->logger()
                            );
                            $view->render();*/
    //                        require 'History/historyEntry.php';
    //                        echo "</br></br>";
                            $entryView->render(
                                [
                                    'entry' => $entry,
                                    'translations' => $translations
                                ]
                            );
                        }
                        ?>
                    </div>
                </div>
            </section>
        </main>
<?php $this->renderFooter(); ?>