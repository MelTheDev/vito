import { Server } from '@/types/server';
import { useState, useEffect } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Button } from '@/components/ui/button';
import { CheckIcon, ChevronsUpDownIcon } from 'lucide-react';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { cn } from '@/lib/utils';
import axios from 'axios';

export default function ServerSelect({
  value,
  valueBy = 'id',
  onValueChange,
  id,
  prefetch,
}: {
  value: string;
  valueBy?: keyof Server;
  onValueChange: (selectedServer?: Server) => void;
  id?: string;
  prefetch?: boolean;
}) {
  const [query, setQuery] = useState('');
  const [open, setOpen] = useState(false);
  const [selected, setSelected] = useState<string>(value);

  useEffect(() => {
    setSelected(value);
  }, [value]);

  const {
    data: servers = [],
    isFetching,
    refetch,
  } = useQuery<Server[]>({
    queryKey: ['servers', query],
    queryFn: async () => {
      const response = await axios.get(route('servers.json', { query: query }));
      return response.data;
    },
    enabled: prefetch,
  });

  const onOpenChange = (open: boolean) => {
    setOpen(open);
    if (open) {
      refetch();
    }
  };

  useEffect(() => {
    if (open && query !== '') {
      const timeoutId = setTimeout(() => {
        refetch();
      }, 300); // Debounce search

      return () => clearTimeout(timeoutId);
    }
  }, [query, open, refetch]);

  const selectedServer = servers.find((server) => String(server[valueBy] as Server[keyof Server]) === selected);

  return (
    <Popover open={open} onOpenChange={onOpenChange}>
      <PopoverTrigger asChild>
        <Button id={id} variant="outline" role="combobox" aria-expanded={open} className="w-full justify-between">
          {selectedServer ? selectedServer.name : 'Select server...'}
          <ChevronsUpDownIcon className="opacity-50" />
        </Button>
      </PopoverTrigger>
      <PopoverContent className="p-0" align="start">
        <Command shouldFilter={false}>
          <CommandInput placeholder="Search server..." value={query} onValueChange={setQuery} />
          <CommandList>
            <CommandEmpty>{isFetching ? 'Searching...' : query === '' ? 'Start typing to search servers' : 'No servers found.'}</CommandEmpty>
            <CommandGroup>
              {servers.map((server: Server) => (
                <CommandItem
                  key={`server-select-${server.id}`}
                  value={String(server[valueBy] as Server[keyof Server])}
                  onSelect={(currentValue) => {
                    const newSelected = currentValue === selected ? '' : currentValue;
                    setSelected(newSelected);
                    setOpen(false);
                    const server = servers.find((s) => String(s[valueBy] as Server[keyof Server]) === newSelected);
                    onValueChange(server);
                  }}
                  className="truncate"
                >
                  {server.name} ({server.ip})
                  <CheckIcon className={cn('ml-auto', selected === String(server[valueBy] as Server[keyof Server]) ? 'opacity-100' : 'opacity-0')} />
                </CommandItem>
              ))}
            </CommandGroup>
          </CommandList>
        </Command>
      </PopoverContent>
    </Popover>
  );
}
