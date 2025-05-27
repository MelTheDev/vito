import { ColumnDef } from '@tanstack/react-table';
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { useForm } from '@inertiajs/react';
import { LoaderCircleIcon, MoreVerticalIcon } from 'lucide-react';
import FormSuccessful from '@/components/form-successful';
import React, { useState } from 'react';
import { CronJob } from '@/types/cronjob';
import { Badge } from '@/components/ui/badge';
import DateTime from '@/components/date-time';
import CronJobForm from '@/pages/cronjobs/components/form';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';

function Delete({ cronJob }: { cronJob: CronJob }) {
  const [open, setOpen] = useState(false);
  const form = useForm();

  const submit = () => {
    form.delete(route('cronjobs.destroy', { server: cronJob.server_id, cronJob: cronJob }), {
      onSuccess: () => {
        setOpen(false);
      },
    });
  };
  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <DropdownMenuItem variant="destructive" onSelect={(e) => e.preventDefault()}>
          Delete
        </DropdownMenuItem>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Delete cronJob</DialogTitle>
          <DialogDescription className="sr-only">Delete cronJob</DialogDescription>
        </DialogHeader>
        <p className="p-4">Are you sure you want to delete this cron job? This action cannot be undone.</p>
        <DialogFooter>
          <DialogClose asChild>
            <Button variant="outline">Cancel</Button>
          </DialogClose>
          <Button variant="destructive" disabled={form.processing} onClick={submit}>
            {form.processing && <LoaderCircleIcon className="animate-spin" />}
            <FormSuccessful successful={form.recentlySuccessful} />
            Delete
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}

function CommandCell({ row }: { row: { original: CronJob } }) {
  const [copySuccess, setCopySuccess] = useState(false);
  const copyToClipboard = () => {
    navigator.clipboard.writeText(row.original.command).then(() => {
      setCopySuccess(true);
      setTimeout(() => {
        setCopySuccess(false);
      }, 2000);
    });
  };

  return (
    <Tooltip>
      <TooltipTrigger asChild>
        <div className="inline-flex cursor-pointer justify-start space-x-2 truncate" onClick={copyToClipboard}>
          <Badge variant={copySuccess ? 'success' : 'outline'} className="block max-w-[150px] overflow-ellipsis">
            {row.original.command}
          </Badge>
        </div>
      </TooltipTrigger>
      <TooltipContent side="top">
        <span className="flex items-center space-x-2">Copy</span>
      </TooltipContent>
    </Tooltip>
  );
}

export const columns: ColumnDef<CronJob>[] = [
  {
    accessorKey: 'command',
    header: 'Command',
    enableColumnFilter: true,
    enableSorting: true,
    cell: ({ row }) => {
      return <CommandCell row={row} />;
    },
  },
  {
    accessorKey: 'frequency',
    header: 'Frequency',
    enableColumnFilter: true,
    enableSorting: true,
  },
  {
    accessorKey: 'created_at',
    header: 'Created at',
    enableColumnFilter: true,
    enableSorting: true,
    cell: ({ row }) => {
      return <DateTime date={row.original.created_at} />;
    },
  },
  {
    accessorKey: 'status',
    header: 'Status',
    enableColumnFilter: true,
    enableSorting: true,
    cell: ({ row }) => {
      return <Badge variant={row.original.status_color}>{row.original.status}</Badge>;
    },
  },
  {
    id: 'actions',
    enableColumnFilter: false,
    enableSorting: false,
    cell: ({ row }) => {
      return (
        <div className="flex items-center justify-end">
          <DropdownMenu modal={false}>
            <DropdownMenuTrigger asChild>
              <Button variant="ghost" className="h-8 w-8 p-0">
                <span className="sr-only">Open menu</span>
                <MoreVerticalIcon />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <CronJobForm serverId={row.original.server_id} cronJob={row.original}>
                <DropdownMenuItem onSelect={(e) => e.preventDefault()}>Edit</DropdownMenuItem>
              </CronJobForm>
              <DropdownMenuSeparator />
              <Delete cronJob={row.original} />
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      );
    },
  },
];
