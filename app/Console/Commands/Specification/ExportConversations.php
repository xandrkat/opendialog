<?php

namespace App\Console\Commands\Specification;

use OpenDialogAi\ConversationBuilder\Conversation;

class ExportConversations extends BaseSpecificationCommand
{
    protected $signature = 'conversations:export {conversation?} {--y|yes} {--active|active}';

    protected $description = 'Export all conversations';

    public function handle()
    {
        $conversationName = $this->argument('conversation');

        if ($this->option('yes')) {
            $continue = true;
        } elseif ($conversationName) {
            $continue = $this->confirm(
                sprintf(
                    'Do you want to export conversation %s?',
                    $conversationName
                )
            );
        } else {
            $continue = $this->confirm('Do you want to export all conversations?');
        }

        if ($continue) {
            if ($conversationName) {
                $conversation = Conversation::where('name', $conversationName)->first();
                $this->exportConversation($conversation);
            } else {
                $activeConversations = config('opendialog.active_conversations');

                $conversations = Conversation::all();

                foreach ($conversations as $conversation) {
                    if (!$this->option('active') || in_array($conversation->name, $activeConversations)) {
                        $this->exportConversation($conversation);
                    }
                }
            }

            $this->info('Export of conversations finished');
        } else {
            $this->info('Bye');
        }
    }

    protected function exportConversation(Conversation $conversation): void
    {
        $this->info(sprintf('Exporting conversation %s', $conversation->name));

        $conversationFileName = "$conversation->name.conv";
        $this->createConversationFile($conversationFileName, $conversation->model);
    }
}